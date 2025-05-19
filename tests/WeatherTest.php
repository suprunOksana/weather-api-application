<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class WeatherTest extends TestCase
{
    public function testWeatherEndpointReturns200()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $request = (new ServerRequestFactory())
            ->createServerRequest('GET', '/api/weather')
            ->withQueryParams(['city' => 'Odesa']);

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $body = (string)$response->getBody();
        $data = json_decode($body, true);

        $this->assertArrayHasKey('temperature', $data);
        $this->assertArrayHasKey('humidity', $data);
        $this->assertArrayHasKey('description', $data);
    }
}
