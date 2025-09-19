<?php

declare(strict_types=1);

// инициализация автолодера и базовых зависимостей
$container = require __DIR__ . '/../app/bootstrap.php';

// создаем экземпляр нашего приложения и запускаем обработку реквеста
$app = new App\Core\App(container: $container);
$app->handle(App\Core\Http\Request::fromGlobals());
