<?php

declare(strict_types=1);

namespace App\Repositories;

use SQLite3;

final class RedirectRepository
{
    public function __construct(private readonly SQLite3 $db)
    {
    }

    public function create(int $contentId, string $oldPath, string $newPath, string $now): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO redirects (content_id, old_path, new_path, created_at) VALUES (:content_id, :old_path, :new_path, :created_at)'
        );
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $statement->bindValue(':old_path', $oldPath, SQLITE3_TEXT);
        $statement->bindValue(':new_path', $newPath, SQLITE3_TEXT);
        $statement->bindValue(':created_at', $now, SQLITE3_TEXT);
        $statement->execute();
        return $this->db->lastInsertRowID();
    }

    public function findActive(string $oldPath): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM redirects WHERE old_path = :old_path AND is_active = 1 LIMIT 1'
        );
        $statement->bindValue(':old_path', $oldPath, SQLITE3_TEXT);
        $row = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        return $row === false ? null : $row;
    }

    public function deactivateForContent(int $contentId): void
    {
        $statement = $this->db->prepare('UPDATE redirects SET is_active = 0 WHERE content_id = :content_id');
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $statement->execute();
    }
}
