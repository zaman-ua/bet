<?php

namespace App\Core\Interface;

interface AuthServiceInterface
{
    public function isLoggedIn(): bool;

    public function login(string $login, string $password, bool $remember = false): bool;

    public function loginById(int $userId, bool $remember = false): void;

    public function logout(): void;

    public function resumeFromRememberCookie(): void;

    public function getUser(): array;

    public function getUserId(): ?int;

    public function isAdmin(): bool;
}