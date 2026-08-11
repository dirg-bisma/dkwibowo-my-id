<?php

declare(strict_types=1);

namespace App\Support;

final class ErrorHandler
{
    public static function register(string $environment): void
    {
        $production = $environment === 'production';

        ini_set('display_errors', $production ? '0' : '1');
        error_reporting(E_ALL);

        set_exception_handler(static function (\Throwable $exception) use ($production): void {
            error_log($exception->__toString());

            Response::html(
                $production
                    ? '<!doctype html><html lang="id"><body><h1>500</h1><p>Terjadi kesalahan internal.</p></body></html>'
                    : '<!doctype html><html lang="id"><body><h1>500</h1><pre>' . htmlspecialchars($exception->__toString(), ENT_QUOTES, 'UTF-8') . '</pre></body></html>',
                500
            );
        });
    }
}
