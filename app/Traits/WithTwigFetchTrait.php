<?php

namespace App\Traits;

use App\Exception\TwigRenderException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Throwable;

/**
 * @property \App\Core\Interface\AuthServiceInterface $authService
 */
trait WithTwigFetchTrait
{
    public function fetch(string $template, array $vars = []) : ?string
    {
        try {
            $twig = $this->initTwig();

            $template = $twig->load($template);

            // подмешиваем ошибки валидации и старые данные что бы можно было отобразить в форме
            return $template->render($vars);

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