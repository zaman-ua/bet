<?php

namespace App\Core\Service;

use App\Interface\UserRepositoryInterface;

final class AuthService
{
    private ?array $user = null;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RememberMeService       $rememberMe,
    ) {
    }

    public function isLoggedIn() : bool
    {
        $this->loadUserFromSession();

        return $this->user !== null;
    }

    public function login(string $login, string $password, bool $remember = false) : bool
    {
        // проверяем наличие пользователя в базе
        $userRow = $this->userRepository->getUserIdPwdByLogin($login);
        if(empty($userRow)) {
            return false;
        }

        // проверяем хеш пароля
        if (!password_verify($password, $userRow['password_hash'])) {
            return false;
        }

        // запоминаемся в сессию
        $this->loginById((int)$userRow['id'], $remember);

        return true;
    }

    public function loginById(int $userId, bool $remember = false): void
    {
        $this->setSessionUserId($userId);
        $this->user = $this->userRepository->getUserById($userId, true) ?: null;

        if ($remember) {
            $this->rememberMe->setCookie($userId);
        }
    }

    public function logout() : void
    {
        $this->clearSession();
        $this->rememberMe->clearCookie();
        $this->user = null;
    }

    public function resumeFromRememberCookie(): void
    {
        if ($this->isLoggedIn()) {
            return;
        }

        $userId = $this->rememberMe->extractUserId();
        if ($userId === null) {
            if ($this->user === null && !empty($_SESSION['uid'])) {
                $this->clearSession();
            }

            return;
        }

        if (!$this->userExists($userId)) {
            $this->logout();
            return;
        }

        $this->loginById($userId);
        $this->rememberMe->setCookie($userId);
    }

    public function getUser(): array
    {
        $this->loadUserFromSession();

        return $this->user ?? [];
    }

    public function getUserId(): ?int
    {
        $this->loadUserFromSession();

        return $this->user['id'] ?? null;
    }

    public function isAdmin(): bool
    {
        $this->loadUserFromSession();

        return isset($this->user['is_admin']) && (int) $this->user['is_admin'] === 1;
    }

    private function loadUserFromSession(): void
    {
        $sessionUserId = $_SESSION['uid'] ?? null;
        if ($sessionUserId === null) {
            $this->user = null;
            return;
        }

        if ($this->user !== null && isset($this->user['id']) && (int) $this->user['id'] === (int) $sessionUserId) {
            return;
        }

        $user = $this->userRepository->getUserById((int) $sessionUserId, true);
        if (empty($user)) {
            $this->clearSession();
            $this->user = null;

            return;
        }

        $this->user = $user;
    }

    private function userExists(int $userId): bool
    {
        $user = $this->userRepository->getUserById($userId, true);

        return $user != [];
    }

    private function setSessionUserId(int $userId): void
    {
        $_SESSION['uid'] = $userId;

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    private function clearSession(): void
    {
        unset($_SESSION['uid']);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $this->user = null;
    }
}