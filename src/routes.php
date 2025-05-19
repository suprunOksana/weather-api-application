<?php

use App\Controllers\WeatherController;
use App\Models\Subscription;
use Slim\App;

return function (App $app) {
    $db = require __DIR__ . '/database.php';

    $subscriptionModel = new Subscription($db);
    $weatherController = new WeatherController($subscriptionModel);

    $app->get('/api/weather', [$weatherController, 'weather']);
    $app->post('/api/subscribe', [$weatherController, 'subscribe']);
    $app->get('/api/confirm/{token}', [$weatherController, 'confirm']);
    $app->get('/api/unsubscribe/{token}', [$weatherController, 'unsubscribe']);
};

