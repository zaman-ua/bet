<?php

use App\Core\Router;
use App\Api\ApiHomeController;
use App\Http\HomeController;
use App\Http\User\LoginController;
use App\Http\User\RegistrationController;
use App\Http\User\LogoutController;

return function (Router $route): void {
    $route->add('GET', '/', HomeController::class);
    $route->add('GET', '/{id:\d+}', [HomeController::class, 'show']);

    $route->add('GET', '/posts/{id:\d+}', ApiHomeController::class);


    $route->add('GET', '/users/registration', [RegistrationController::class, 'index']);
    $route->add('POST', '/users/registration', [RegistrationController::class, 'register']);

    $route->add('GET', '/users/login', [LoginController::class, 'index']);
    $route->add('POST', '/users/login', [LoginController::class, 'login']);

    $route->add('GET', '/users/logout', [LogoutController::class, 'index']);
    $route->add('POST', '/users/logout', [LogoutController::class, 'index']);
};