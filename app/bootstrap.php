<?php

declare(strict_types=1);

// автозагрузка через composer
use App\Core\Auth;

require __DIR__ . '/../vendor/autoload.php';

// константа для рутовой директории проекта
define('APP_ROOT', realpath(__DIR__ . '/..'));

// для удобства работы с .env файлом
// так же есть хелпер с дефолтным значением
$env = APP_ROOT . '/.env';
if (is_file($env)) {
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        // выше пропускаем пустые строки, а здесь строки комментариев начинающиеся символом #
        if ($line[0] === '#') continue;

        // разделяем ключ значение по первому знаку равно
        [$k,$v] = array_map('trim', explode('=', $line, 2));

        // подрезаем кавычки, что бы получить чистые строки
        $v = trim($v, "\"'");

        // записываем в глобальный массив $_ENV
        $_ENV[$k] = $v;

        // в окружение думаю записывать не стоит в данном проекте
        //putenv("$k=$v");
    }
}

require APP_ROOT . '/app/helpers.php';

// тут сразу же своим хелпером и воспользуемся
date_default_timezone_set(env('APP_TZ', 'UTC'));

// заполняем настройки подключения к базе,
// но само подключение произойдет по месту использования
App\Core\Db\Db::configure(require APP_ROOT . '/config/database.php');

// не хороший тон стартовать сессию в каждом инстансе приложения
// выносим в абстрактный контроллер для http запросов, а для api сессия не нужна

// возможно позже сделаю лучше
session_start();
Auth::resumeFromRememberCookie();
