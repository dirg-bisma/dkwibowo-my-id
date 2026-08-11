<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

final class Slug
{
    private const RESERVED = [
        'admin', 'assets', 'database', 'content', 'sitemap', 'robots', 'api', 'tag',
    ];

    public static function assertValid(string $slug): void
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            throw new InvalidArgumentException('Invalid slug.');
        }

        if (in_array($slug, self::RESERVED, true)) {
            throw new InvalidArgumentException('Reserved slug.');
        }
    }

    public static function isValid(string $slug): bool
    {
        try {
            self::assertValid($slug);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
