<?php

namespace App\Core;

use App\Core\Http\Response;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\Exception\ErrorHandler;
use App\Http\Controller;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class App
{
//    public function __construct(private ?Router $router = null, private ?Container $container = null)
//    {
//        // инициализация роутера
//        $this->router = $router ?? Router::fromFile(APP_ROOT . '/routes/routes.php');
//
//        // инициализация DI-контейнера
//        $this->container = $this->container ?? new Container();
//    }

    public function __construct(
        private Router $router,
        private CsrfGuard $csrfGuard,
        private ControllerInvoker $controllerInvoker,
        private ResponseEmitter $responseEmitter,
    ) {}

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

            $this->responseEmitter->emit($response);
            return;
        }

        // проверка csrf для всех POST запросов
        $csrfResponse = $this->csrfGuard->validate($request, $response);
        if ($csrfResponse !== null) {
            $this->responseEmitter->emit($csrfResponse);
            return;
        }

        [$handler, $vars] = $match;

        // переменные с роутера вписываем в реквест
        if(!empty($vars)) {
            foreach ($vars as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }
        }

        $response = $this->controllerInvoker->invoke($handler, $request, $response);
        $this->responseEmitter->emit($response);
    }
}