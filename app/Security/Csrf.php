<?php

declare(strict_types=1);

namespace App\Security;

use App\Support\Response;

final class Csrf
{
    public function token(): string
    {
        if (!isset($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public function assertValid(?string $token): void
    {
        if (!is_string($token) || !isset($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $token)) {
            Response::text('Invalid CSRF token.', 422);
        }
    }

    public function invalidate(): void
    {
        unset($_SESSION['_csrf']);
    }
}
