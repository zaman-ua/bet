<?php

namespace App\Http\User;

use App\Core\Interface\AuthServiceInterface;
use App\Core\Interface\RequestInterface;
use App\Core\Interface\ResponseInterface;
use App\DTO\UserCreateDTO;
use App\Http\Controller;
use App\Interface\UserReaderRepositoryInterface;
use App\Interface\UserWriterRepositoryInterface;
use App\Traits\WithRequestValidateTrait;
use App\Traits\WithTwigRenderTrait;

class RegistrationController extends Controller
{
    use WithTwigRenderTrait;
    use WithRequestValidateTrait;

    public function __construct(
        RequestInterface                               $request,
        ResponseInterface                              $response,
        AuthServiceInterface                           $authService,
        private readonly UserReaderRepositoryInterface $userReaderRepository,
        private readonly UserWriterRepositoryInterface $userWriterRepository,
    ) {
        parent::__construct($request, $response, $authService);
    }
    public function index() : ResponseInterface
    {
        return $this->render('user/registration.html.twig');
    }

    public function store() : ResponseInterface
    {
        // общая валидация
        $validated = $this->validate($this->request->getPost()['data'], [
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
            $userExist = $this->userReaderRepository->getUserIdByLogin($validated['login']);
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
            $userId = $this->userWriterRepository->createUser(new UserCreateDTO(
                login: $validated['login'],
                password_hash: $password_hash,
                name: $validated['name'],
                gender: $validated['gender'],
                birth_date: $validated['birth_date']
            ));

            // вставляем контакты в отдельную табличку
            // без валидации данных это все очень плохо
            if(!empty($this->request->getPost()['contacts'])) {
                foreach($this->request->getPost()['contacts'] as $contact) {
                    $this->userWriterRepository->createUserContact(
                        $userId,
                        $contact['type'],
                        $contact['value'],
                    );
                }
            }

            // сразу его и авторизуем
            // запоминаемся в сессию
            $this->authService->loginById($userId);

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