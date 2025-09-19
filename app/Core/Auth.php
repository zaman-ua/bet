<?php

namespace App\Core;

use App\Interface\UserRepositoryInterface;
use RuntimeException;

final class Auth
{
    private static ?array $user = null;
    private static ?UserRepositoryInterface $users = null;

    public const REMEMBER_COOKIE = 'remember';

    public static function setUserRepository(UserRepositoryInterface $userRepository): void
    {
        self::$users = $userRepository;
    }

    public static function usersRepositoryInstance() : UserRepositoryInterface
    {
        if (self::$users === null) {
            throw new RuntimeException('User repository dependency is not set.');
        }

        return self::$users;
    }

    public static function isLoggedIn() : bool
    {
        if(!empty($_SESSION['uid'])) {
            // пользователь авторизован - достанем по нему немного информации, что бы всегда была под рукой
            // заодно и проверим валидный ли у нас идентификатор в сессии

            // так как функция может дергаться везеде по коду, то исключим повторные запросы в бд
            if(empty(self::$user)) {
                self::$user = self::userExists($_SESSION['uid']);
            }

            // вот и сама проверка валидности пользователя
            if(!empty(self::$user['id'])) {
                return true;
            }
        }

        return false;
    }

    public static function userExists($userId) : ?array
    {
        $user = self::usersRepositoryInstance()->getUserById($userId, true);

        return $user == [] ? null : $user;
    }

    // запомнить в сессию
    public static function loginUser(int $uid) : void
    {
        $_SESSION['uid'] = $uid;
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // убрать из сессии
    public static function logoutUser() : void
    {
        unset($_SESSION['uid']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    // запомнить в куки
    public static function setRememberCookie(int $uid, int $days = 30) : void
    {
        $exp     = time() + $days * 86400;
        $payload = $uid . '|' . $exp;
        $sig     = hash_hmac('sha256', $payload, env('APP_SECRET'));
        $value   = base64_encode($payload . '|' . $sig);

        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $exp,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']), // в проде: true всегда
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    // убрать из кук
    public static function clearRememberCookie() : void
    {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public static function resumeFromRememberCookie() : void
    {
        if (self::isLoggedIn()) return;

        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if (empty($raw)) {

            // если в сессии ид есть, а в куках и базе нет, то удаляем - потому что это ошибка
            if(empty(self::$user) && !empty($_SESSION['uid'])) {
                self::logoutUser();
            }

            return;
        }

        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            self::clearRememberCookie();
            return;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            self::clearRememberCookie();
            return;
        }

        [$uid, $exp, $sig] = $parts;
        if (!ctype_digit($uid) || !ctype_digit($exp)) {
            self::clearRememberCookie();
            return;
        }

        if ((int)$exp < time()) {
            self::clearRememberCookie();
            return;
        }

        $payload = $uid . '|' . $exp;
        $check = hash_hmac('sha256', $payload, env('APP_SECRET'));
        if (!hash_equals($check, $sig)) {
            self::clearRememberCookie();
            return;
        }

        // проверить что пользователь ещё существует
        if (!self::userExists((int)$uid)) {
            self::logoutUser();
            self::clearRememberCookie();
            return;
        }

        self::loginUser((int)$uid);
        // можно “ротировать” куку, обновив срок:
        self::setRememberCookie((int)$uid, 30);
    }

    // проверка в базе и авторизация
    public static function login(string $login, string $password, bool $remember = false) : bool
    {
        // проверяем наличие пользователя в базе
        $userRow = self::usersRepositoryInstance()->getUserIdPwdByLogin($login);
        if(empty($userRow)) {
            return false;
        }

        // проверяем хеш пароля
        if (!password_verify($password, $userRow['password_hash'])) {
            return false;
        }

        // запоминаемся в сессию
        self::loginUser((int)$userRow['id']);

        // запоминаемся в куки если стояла галочка
        if ($remember) {
            self::setRememberCookie((int)$userRow['id']);
        }

        return true;
    }

    public static function getUser() : array
    {
        return self::$user ?? [];
    }

    public static function getUserId() : ?int
    {
        return self::$user !== null ? self::$user['id'] : null;
    }

    public static function isAdmin() : bool
    {
        return isset(self::$user['is_admin']) && self::$user['is_admin'] == 1;
    }
}
