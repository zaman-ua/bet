<?php

namespace App\Core;

use App\Core\Db\Db;

final class Auth
{
    private static ?array $user = null;

    public const REMEMBER_COOKIE = 'remember';

    public static function isLoggedIn() : bool
    {
        return isset($_SESSION['uid']);
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
        if (self::isLoggedIn()) {
            // пользователь авторизован - достанем по нему немного информации, что бы всегда была под рукой
            self::$user = Db::getRow('SELECT id, login, name, status FROM `users` WHERE `id` = ?', [$_SESSION['uid']]);

            return;
        }

        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if (empty($raw)) return;

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

        // опционально: проверить что пользователь ещё существует
        // if (!userExists((int)$uid)) { clearRememberCookie(); return; }

        self::loginUser((int)$uid);
        // можно “ротировать” куку, обновив срок:
        self::setRememberCookie((int)$uid, 30);
    }

    // проверка в базе и авторизация
    public static function login(string $login, string $password, bool $remember = false) : bool
    {
        // проверяем наличие пользователя в базе
        $userRow = Db::getRow("SELECT id, password_hash FROM users WHERE login = :login AND status = 'active' ", ['login' => $login]);
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
}
