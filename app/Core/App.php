<?php

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Exception\ErrorHandler;

final class App
{
    public function __construct(private ?Router $router = null)
    {
        // инициализация роутера
        $this->router = $router ?? Router::fromFile(APP_ROOT . '/routes/routes.php');
    }

    public function handle(Request $request) : void
    {
        $match = $this->router->match($request);
        $response = new Response();

        if (empty($match)) {
            // для не найденных маршрутов, сразу отдаем 404 без шаблона
            $response = $response
                ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                ->withStatus(404)
                ->write('404 Not Found');

            $this->emit($response);
            return;
        }

        [$handler, $vars] = $match;

        // переменные с роутера вписываем в реквест
        if(!empty($vars)) {
            foreach ($vars as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        }

        // оборачиваем вызов контроллеров проверкой на исключения
        try {
            // вызов контроллера
            if (is_string($handler)) {
                // для инвокабл контроллера
                $controller = new $handler($request, $response);
                $response = $controller();

            } elseif (is_array($handler)) {
                // для конкретного метода контроллера
                [$class, $method] = [$handler[0], $handler[1]];
                $controller = new $class($request, $response);
                $response = $controller->{$method}();

            }
        } catch (\Throwable $e) {
            // кастомный обработчик исключений,
            // что бы красиво вывести для api и браузерных запросов
            $errorHandler = new ErrorHandler(env('APP_DEBUG', false));
            $response = $errorHandler->render($e, $request, $response);
        }

        // обработка респонса
        $this->emit($response);
    }

    private function emit(Response $response): void
    {
        // устанавливаем правильный код ответа
        http_response_code($response->status());

        // устанавливаем заголовки
        foreach ($response->headers() as $name => $value) {
            header("$name: $value", true);
        }

        // отображаем контент
        echo $response->body();
    }
}