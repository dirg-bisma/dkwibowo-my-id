<?php

declare(strict_types=1);

$path = getenv('DB_PATH') ?: 'database/indexer.sqlite';

return [
    'path' => str_starts_with($path, '/') ? $path : dirname(__DIR__) . '/' . ltrim($path, '/'),
];
