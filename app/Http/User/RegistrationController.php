<?php

namespace App\Http\User;

use App\Core\Auth;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\DTO\UserCreateDTO;
use App\Http\Controller;
use App\Repository\UserRepository;

class RegistrationController extends Controller
{
    private UserRepository $userRepository;

    public function __construct(Request $request, Response $response) {
        $this->userRepository = new UserRepository();

        parent::__construct($request, $response);
    }
    public function index() : Response
    {
        return $this->render('user/registration.html.twig');
    }

    public function register() : Response
    {
        // общая валидация
        $validated = $this->validate($this->request->post['data'], [
            'login'             => ['required', 'string', 'min:3', 'max:50'],
            'password'          => ['required', 'string', 'min:8'],
            'retype_password'   => ['required', 'string', 'min:8'],
            'name'              => ['required', 'string', 'min:3', 'max:100'],
            'gender'            => ['required', 'in:o,m,f'],
            'birth_date'        => ['date:Y-m-d'],
        ]);

        // валидация совпадения пароля
        if($validated['password'] !== $validated['retype_password']) {
            // проверка, что бы не стереть предыдущую ошибку
            if(!in_array('retype_password', array_keys($this->errors))) {
                $this->errors['retype_password'] = 'Пароли не совпадают';
            }
        }

        // проверим на существование логин пользователя
        if(!empty($validated['login'])) {
            $userExist = $this->userRepository->getUserIdByLogin($validated['login']);
            if(!empty($userExist)) {
                if(!in_array('login', array_keys($this->errors))) {
                    $this->errors['login'] = 'такой login уже существует';
                }
            }
        }

        // есть ошибки, дальше регистрация не работает
        if(!empty($this->errors)) {
            if($this->request->wantsJson()) {
                return $this->json(['errors' => $this->errors], 422);
            } else {
                return $this->render('user/registration.html.twig');
            }
        }

        // ошибок нет, продолжаем регистрацию
        $password_hash = password_hash($validated['password'], PASSWORD_DEFAULT);

        try {
            // вставляем пользователя
            $userId = $this->userRepository->createUser(new UserCreateDTO(
                login: $validated['login'],
                password_hash: $password_hash,
                name: $validated['name'],
                gender: $validated['gender'],
                birth_date: $validated['birth_date']
            ));

            // вставляем контакты в отдельную табличку
            // без валидации данных это все очень плохо
            if(!empty($this->request->post['contacts'])) {
                foreach($this->request->post['contacts'] as $contact) {
                    $this->userRepository->createUserContact(
                        $userId,
                        $contact['type'],
                        $contact['value'],
                    );
                }
            }

            // сразу его и авторизуем
            // запоминаемся в сессию
            Auth::loginUser($userId);

            if($this->request->wantsJson()) {
                return $this->json(['ok' => true]);
            } else {
                return $this->render('user/registration.html.twig');
            }

        } catch (\Throwable $e) {
            $this->errors['error'] = $e->getMessage();

            if($this->request->wantsJson()) {
                return $this->json(['errors' => $this->errors], 422);
            } else {
                return $this->render('user/registration.html.twig');
            }
        }
    }
}