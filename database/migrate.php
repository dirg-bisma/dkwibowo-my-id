<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
}

if (class_exists(Dotenv\Dotenv::class) && is_file($root . '/.env')) {
    Dotenv\Dotenv::createImmutable($root)->safeLoad();
}

$config = require $root . '/config/database.php';
$databaseDirectory = dirname($config['path']);

if (!is_dir($databaseDirectory) && !mkdir($databaseDirectory, 0775, true) && !is_dir($databaseDirectory)) {
    throw new RuntimeException('Unable to create database directory.');
}

$db = new SQLite3($config['path']);
$db->enableExceptions(true);
$db->exec('PRAGMA foreign_keys = ON');
$db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS migrations (
    name TEXT PRIMARY KEY,
    applied_at DATETIME NOT NULL
)
SQL);

$migrationDirectory = $root . '/database/migrations';
$files = glob($migrationDirectory . '/*.sql') ?: [];
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    $statement = $db->prepare('SELECT 1 FROM migrations WHERE name = :name LIMIT 1');
    $statement->bindValue(':name', $name, SQLITE3_TEXT);
    $alreadyApplied = $statement->execute()->fetchArray(SQLITE3_NUM) !== false;

    if ($alreadyApplied) {
        continue;
    }

    $db->exec('BEGIN');

    try {
        $db->exec(file_get_contents($file));
        $statement = $db->prepare('INSERT INTO migrations (name, applied_at) VALUES (:name, :applied_at)');
        $statement->bindValue(':name', $name, SQLITE3_TEXT);
        $statement->bindValue(':applied_at', gmdate('Y-m-d H:i:s'), SQLITE3_TEXT);
        $statement->execute();
        $db->exec('COMMIT');
        fwrite(STDOUT, "Applied {$name}\n");
    } catch (Throwable $exception) {
        $db->exec('ROLLBACK');
        throw $exception;
    }
}

$db->close();
fwrite(STDOUT, "Database is up to date.\n");
