<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;


class SubscribeTest extends TestCase
{
    public function testSubscribeReturns200()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $payload = json_encode([
            'email' => 'test@example.com',
            'city' => 'Odesa',
            'frequency' => 'daily',
        ]);

        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream($payload);

        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/api/subscribe')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
