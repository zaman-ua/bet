<?php

use App\Api\ApiHomeController;
use App\Core\Router;
use App\Http\HomeController;

return function (Router $route): void {
    $route->add('GET', '/', HomeController::class);
    $route->add('GET', '/{id:\d+}', [HomeController::class, 'show']);

    $route->add('GET', '/posts/{id:\d+}', ApiHomeController::class);
};