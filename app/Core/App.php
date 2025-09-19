<?php

namespace App\Core;

use App\Core\Http\RequestInterface;
use App\Core\Http\Response;
use App\Core\Http\ResponseInterface;
use App\Exception\ErrorHandler;
use App\Http\Controller;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class App
{
    public function __construct(private ?Router $router = null, private ?Container $container = null)
    {
        // инициализация роутера
        $this->router = $router ?? Router::fromFile(APP_ROOT . '/routes/routes.php');

        // инициализация DI-контейнера
        $this->container = $this->container ?? new Container();
    }

    public function handle(RequestInterface $request) : void
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

        // проверка csrf для всех POST запросов
        if($request->method === 'POST') {
            if (($request->post['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
                $response = $response
                    ->withHeader('Content-Type', 'text/plain; charset=utf-8')
                    ->withStatus(419)
                    ->write('CSRF token mismatch');
                $this->emit($response);
                return;
            }
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
                $controller = $this->resolveController($handler, $request, $response);
                $response = $controller();

            } elseif (is_array($handler)) {
                // для конкретного метода контроллера
                [$class, $method] = [$handler[0], $handler[1]];
                $controller = $this->resolveController($class, $request, $response);
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

    private function resolveController(string $class, RequestInterface $request, ResponseInterface $response): Controller
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            $controller = $reflection->newInstance($request, $response);

            return $controller;
        }

        $parameters = $constructor->getParameters();

        if (count($parameters) < 2) {
            throw new RuntimeException("Controller '{$class}' must accept Request and Response as the first arguments");
        }

        $arguments = [$request, $response];

        foreach (array_slice($parameters, 2) as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->container->get($type->getName());

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException("Cannot resolve dependency '{$parameter->getName()}' for controller '{$class}'");
        }

        $controller = $reflection->newInstanceArgs($arguments);

        return $controller;
    }

    private function emit(ResponseInterface $response): void
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