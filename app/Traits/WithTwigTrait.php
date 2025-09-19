<?php

namespace App\Traits;

use App\Core\Http\ResponseInterface;
use App\Exception\TwigRenderException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Throwable;

/**
 * @property \App\Core\Interface\AuthServiceInterface $authService
 */
trait WithTwigTrait
{
// такие контроллеры у нас используются для выдачи html
    // по этому здесь и подключаем шаблонизатор
    // сразу же формируем респонс с правильным кодом ответа
    // в контроллере в конце нужно будет только вызвать render() с указанием шаблона и передать переменные шаблона
    public function render(string $template, array $vars = [], int $code = 200) : ResponseInterface
    {
        try {
            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            $body = $this->fetch($template, $vars);

        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }

        return $this->html($body, $code);
    }

    public function fetch(string $template, array $vars = []) : ?string
    {
        try {
            $twig = $this->initTwig();

            $template = $twig->load($template);

            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            return $template->render(array_merge($vars ?? [], [
                'old' => $this->oldData,
                'errors' => $this->errors,
            ]));

        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }
    }

    private function initTwig(): Environment
    {
        try {
            $loader = new FilesystemLoader(APP_ROOT . env('TWIG_TEMPLATE', '/templates'));
            $twig = new Environment($loader, [
                //'cache' => APP_ROOT . env('TWIG_TEMPLATE_CACHE', '/templates/templates_c')
                'autoescape' => 'html' // помогает от xss
            ]);

            $twig->addFunction(new TwigFunction('env', 'env'));
            $twig->addFunction(new TwigFunction('assets', 'assets'));
            $twig->addFunction(new TwigFunction('csrf_token', 'csrf_token'));
            $twig->addFunction(new TwigFunction('var_dump', 'var_dump'));
            $twig->addFunction(new TwigFunction('isLoggedIn', fn (): bool => $this->authService->isLoggedIn()));
            $twig->addFunction(new TwigFunction('getUser', fn (): array => $this->authService->getUser()));
            $twig->addFunction(new TwigFunction('isAdmin', fn (): bool => $this->authService->isAdmin()));
            $twig->addFunction(new TwigFunction('getUserId', fn (): ?int => $this->authService->getUserId()));

            return $twig;
        } catch (Throwable $e) {
            throw new TwigRenderException($e->getMessage());
        }
    }
}