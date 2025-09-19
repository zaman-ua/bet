<?php

use App\Core\Router;
use App\Http\BetController;
use App\Http\HomeController;
use App\Http\User\LoginController;
use App\Http\User\RegistrationController;
use App\Http\User\LogoutController;

use App\Http\Admin;

return function (Router $route): void {
    // главная
    $route->add('GET', '/', HomeController::class);

    // регистация
    $route->add('GET', '/users/registration', [RegistrationController::class, 'index']);
    $route->add('POST', '/users/registration', [RegistrationController::class, 'register']);

    // вход
    $route->add('GET', '/users/login', [LoginController::class, 'index']);
    $route->add('POST', '/users/login', [LoginController::class, 'login']);

    // что бы и так и так принимало
    $route->add('GET', '/users/logout', [LogoutController::class, 'index']);
    $route->add('POST', '/users/logout', [LogoutController::class, 'index']);

    // поставить ставку
    $route->add('POST', '/users/bet', [BetController::class, 'store']);

    // админка ставки
    $route->add('GET', '/admin/bets', [Admin\BetsController::class, 'index']);
    $route->add('POST', '/admin/bets/{id:\d+}/play', [Admin\BetsController::class, 'play']);

    // админка пользователи
    $route->add('GET', '/admin/users', [Admin\UsersController::class, 'index']);
    $route->add('POST', '/admin/users/{id:\d+}/adjust', [Admin\UsersController::class, 'adjust']);

    // админка фин. лог
    $route->add('GET', '/admin/amount_logs', Admin\AmountLogsController::class);
};