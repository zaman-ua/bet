<?php

declare(strict_types=1);

// автозагрузка через composer
use App\Core\Interface\AuthServiceInterface;

require __DIR__ . '/../vendor/autoload.php';

// константа для рутовой директории проекта
define('APP_ROOT', realpath(__DIR__ . '/..'));

require APP_ROOT . '/app/helpers.php';

if(env('APP_DEBUG', false)) {
    ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING );
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// тут сразу же своим хелпером и воспользуемся
date_default_timezone_set(env('APP_TZ', 'UTC'));

// заполняем настройки подключения к базе,
// но само подключение произойдет по месту использования
$databaseConfig = require APP_ROOT . '/config/database.php';

// не хороший тон стартовать сессию в каждом инстансе приложения
// выносим в абстрактный контроллер для http запросов, а для api сессия не нужна
// возможно позже сделаю лучше
session_start();

// переносим ниже, где уже работает контейнер
//Auth::resumeFromRememberCookie();

$container = require APP_ROOT . '/app/container.php';


// то что переносили свыше Auth::resumeFromRememberCookie();
$container->get(AuthServiceInterface::class)->resumeFromRememberCookie();

return $container;