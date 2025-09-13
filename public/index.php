<?php

declare(strict_types=1);

ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING );
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// инициализация автолодера и базовых зависимостей
require __DIR__ . '/../app/bootstrap.php';

// создаем экземпляр нашего приложения и запускаем обработку реквеста
$app = new App\Core\App();
$app->handle(App\Core\Http\Request::fromGlobals());
