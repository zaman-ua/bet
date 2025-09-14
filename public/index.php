<?php

declare(strict_types=1);

// инициализация автолодера и базовых зависимостей
require __DIR__ . '/../app/bootstrap.php';

// создаем экземпляр нашего приложения и запускаем обработку реквеста
$app = new App\Core\App();
$app->handle(App\Core\Http\Request::fromGlobals());
