<?php

use App\Core\Router;
use App\Http\BetController;
use App\Http\HomeController;
use App\Http\User\LoginController;
use App\Http\User\RegistrationController;
use App\Http\User\LogoutController;

use App\Http\Admin;

return function (Router $route): void {
    $route->add('GET', '/', HomeController::class);
    $route->add('GET', '/{id:\d+}', [HomeController::class, 'show']);


    $route->add('GET', '/users/registration', [RegistrationController::class, 'index']);
    $route->add('POST', '/users/registration', [RegistrationController::class, 'register']);

    $route->add('GET', '/users/login', [LoginController::class, 'index']);
    $route->add('POST', '/users/login', [LoginController::class, 'login']);

    $route->add('GET', '/users/logout', [LogoutController::class, 'index']);
    $route->add('POST', '/users/logout', [LogoutController::class, 'index']);

    $route->add('POST', '/users/bet', [BetController::class, 'store']);


    $route->add('GET', '/admin/bets', Admin\BetsController::class);


    $route->add('GET', '/admin/users', Admin\UsersController::class);
    $route->add('POST', '/admin/users/{id:\d+}/adjust', [Admin\UsersController::class, 'adjust']);
};