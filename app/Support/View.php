<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class View
{
    public function __construct(private readonly string $root)
    {
    }

    public function page(string $name, array $data = []): string
    {
        $view = $this->root . '/views/' . $name . '.php';

        if (!is_file($view)) {
            throw new RuntimeException('View not found.');
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $view;
        $body = (string) ob_get_clean();

        $layout = $this->root . '/views/layouts/main.php';
        extract(array_merge($data, ['body' => $body]), EXTR_SKIP);
        ob_start();
        require $layout;
        return (string) ob_get_clean();
    }
}
