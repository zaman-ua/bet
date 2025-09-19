<?php

namespace App\Core\Service;

final class RememberMeService
{
    private const REMEMBER_COOKIE = 'remember';

    public function __construct(
        private readonly string $appSecret,
    ) {
    }

    public function setCookie(int $userId, int $days = 30): void
    {
        $expiresAt = time() + $days * 86400;
        $payload = $userId . '|' . $expiresAt;
        $signature = hash_hmac('sha256', $payload, $this->appSecret);
        $value = base64_encode($payload . '|' . $signature);

        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $expiresAt,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function clearCookie(): void
    {
        setcookie(self::REMEMBER_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    public function extractUserId(): ?int
    {
        $raw = $_COOKIE[self::REMEMBER_COOKIE] ?? null;
        if (empty($raw)) {
            return null;
        }

        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            $this->clearCookie();
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            $this->clearCookie();
            return null;
        }

        [$userId, $expiresAt, $signature] = $parts;
        if (!ctype_digit($userId) || !ctype_digit($expiresAt)) {
            $this->clearCookie();
            return null;
        }

        if ((int)$expiresAt < time()) {
            $this->clearCookie();
            return null;
        }

        $payload = $userId . '|' . $expiresAt;
        $expectedSignature = hash_hmac('sha256', $payload, $this->appSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            $this->clearCookie();
            return null;
        }

        return (int) $userId;
    }
}