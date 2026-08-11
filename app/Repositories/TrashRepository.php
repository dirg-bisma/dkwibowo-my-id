<?php

declare(strict_types=1);

namespace App\Repositories;

use SQLite3;

final class TrashRepository
{
    public function __construct(private readonly SQLite3 $db)
    {
    }

    public function create(int $contentId, string $originalStatus, string $originalSlug, string $now): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO content_trash (content_id, original_status, original_slug, trashed_at) VALUES (:content_id, :original_status, :original_slug, :trashed_at)'
        );
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $statement->bindValue(':original_status', $originalStatus, SQLITE3_TEXT);
        $statement->bindValue(':original_slug', $originalSlug, SQLITE3_TEXT);
        $statement->bindValue(':trashed_at', $now, SQLITE3_TEXT);
        $statement->execute();
        return $this->db->lastInsertRowID();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM content_trash WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $row = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        return $row === false ? null : $row;
    }

    public function listAll(): array
    {
        $result = $this->db->query(<<<'SQL'
            SELECT tr.*, c.title_id, c.title_en, c.slug
            FROM content_trash tr
            JOIN content c ON c.id = tr.content_id
            ORDER BY tr.trashed_at DESC, tr.id DESC
        SQL);
        $rows = [];
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM content_trash WHERE id = :id');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->execute();
    }
}
