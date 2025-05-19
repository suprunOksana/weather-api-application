<?php

namespace App\Controllers;

use App\Models\Subscription;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WeatherController
{
    private Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function weather(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $city = trim($queryParams['city'] ?? '');

        if (!$city) {
            $response->getBody()->write(json_encode(['error' => 'Invalid request']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $apiKey = $_ENV['WEATHER_API_KEY'] ?? '';
        $url = "https://api.weatherapi.com/v1/current.json?key={$apiKey}&q=" . urlencode($city);

        $weatherData = @file_get_contents($url);
        $data = json_decode($weatherData, true);

        if (isset($data['error'])) {
            $response->getBody()->write(json_encode(['error' => $data['error']['message'] ?? 'City not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $result = [
            'temperature' => $data['current']['temp_c'] ?? null,
            'humidity' => $data['current']['humidity'] ?? null,
            'description' => $data['current']['condition']['text'] ?? null,
        ];

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function subscribe(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $city = trim($data['city'] ?? '');
        $frequency = $data['frequency'] ?? '';

        if (!$email || !$city || !in_array($frequency, ['hourly', 'daily'])) {
            $response->getBody()->write(json_encode(['error' => 'Invalid input']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $token = bin2hex(random_bytes(16));

        try {
            $this->subscription->create($email, $city, $frequency, $token);
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode(['error' => 'Email already subscribed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        $this->sendConfirmationEmail($email, $token);

        $response->getBody()->write(json_encode(['message' => 'Subscription successful. Confirmation email sent.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function confirm(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'] ?? '';
        if (!$token) {
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->subscription->findByToken($token);
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Token not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $this->subscription->confirmByToken($token);

        $response->getBody()->write(json_encode(['message' => 'Subscription confirmed successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function unsubscribe(Request $request, Response $response, array $args): Response
    {
        $token = $args['token'] ?? '';
        if (!$token) {
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->subscription->findByToken($token);
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Token not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $this->subscription->deleteByToken($token);

        $response->getBody()->write(json_encode(['message' => 'Unsubscribed successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function sendConfirmationEmail(string $email, string $token): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom('no-reply@weatherapi.app', 'Weather API');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirm your Weather Subscription';
            $mail->Body = 'Click to confirm your subscription: <a href="http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/api/confirm/' . $token . '">Confirm</a>';

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            file_put_contents(__DIR__ . '/../../mail_log.txt', "Mailer exception: " . $e->getMessage() . "\n", FILE_APPEND);//        }
        }
    }
}
