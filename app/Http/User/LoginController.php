<?php

namespace App\Http\User;

use App\Core\Http\ResponseInterface;
use App\Http\Controller;
use App\Traits\WithRequestValidateTrait;
use App\Traits\WithTwigTrait;

class LoginController extends Controller
{
    use WithTwigTrait;
    use WithRequestValidateTrait;

    public function index() : ResponseInterface
    {
        $this->authService->logout();

        return $this->render('user/login.html.twig');
    }

    public function login() : ResponseInterface
    {
        // общая валидация
        $validated = $this->validate($this->request->getPost()['data'], [
            'login'             => ['required', 'string', 'min:3', 'max:50'],
            'password'          => ['required', 'string', 'min:8'],
            'remember_me'       => ['required', 'boolean'],
        ]);

        // есть ошибки,
        if(!empty($this->errors)) {
            return $this->json(['errors' => $this->errors], 422);
        }

        $isOk = $this->authService->login($validated['login'], $validated['password'], $validated['remember_me']);
        if(!$isOk) {
            return $this->json(['errors' => [
                'errors' => 'Пользователь не существует или пароль не верен'
            ]], 422);
        }

        // тут бы правильный редирект для админа сделать
        if($this->authService->isAdmin()) {
            return $this->json([
                'ok' => true,
                'redirect' => '/admin/bets'
            ]);
        }

        return $this->json(['ok' => true]);
    }
}