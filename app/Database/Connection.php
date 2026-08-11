<?php

declare(strict_types=1);

namespace App\Database;

use SQLite3;

final class Connection
{
    public static function open(string $path): SQLite3
    {
        $db = new SQLite3($path);
        $db->enableExceptions(true);
        $db->exec('PRAGMA foreign_keys = ON');
        return $db;
    }
}
