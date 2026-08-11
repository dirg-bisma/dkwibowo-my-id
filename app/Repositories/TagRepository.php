<?php

declare(strict_types=1);

namespace App\Repositories;

use SQLite3;

final class TagRepository
{
    public function __construct(private readonly SQLite3 $db)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM tags WHERE slug = :slug LIMIT 1');
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        $row = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        return $row === false ? null : $row;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM tags WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $row = $statement->execute()->fetchArray(SQLITE3_ASSOC);
        return $row === false ? null : $row;
    }

    public function listAll(): array
    {
        $result = $this->db->query('SELECT * FROM tags ORDER BY name COLLATE NOCASE');
        $rows = [];
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function listPublishedSlugs(): array
    {
        $result = $this->db->query(<<<'SQL'
            SELECT DISTINCT t.slug
            FROM tags t
            JOIN content_tags ct ON ct.tag_id = t.id
            JOIN content c ON c.id = ct.content_id
            WHERE c.status = 'published'
            ORDER BY t.slug
        SQL);
        $slugs = [];
        while (($row = $result->fetchArray(SQLITE3_NUM)) !== false) {
            $slugs[] = $row[0];
        }
        return $slugs;
    }

    public function create(string $name, string $slug, string $now): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO tags (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)'
        );
        $statement->bindValue(':name', $name, SQLITE3_TEXT);
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        $statement->bindValue(':created_at', $now, SQLITE3_TEXT);
        $statement->bindValue(':updated_at', $now, SQLITE3_TEXT);
        $statement->execute();
        return $this->db->lastInsertRowID();
    }

    public function hasContent(int $tagId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM content_tags WHERE tag_id = :tag_id LIMIT 1');
        $statement->bindValue(':tag_id', $tagId, SQLITE3_INTEGER);
        return $statement->execute()->fetchArray(SQLITE3_NUM) !== false;
    }

    public function update(int $id, string $name, string $slug, string $now): void
    {
        $statement = $this->db->prepare(
            'UPDATE tags SET name = :name, slug = :slug, updated_at = :updated_at WHERE id = :id'
        );
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->bindValue(':name', $name, SQLITE3_TEXT);
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        $statement->bindValue(':updated_at', $now, SQLITE3_TEXT);
        $statement->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM tags WHERE id = :id');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->execute();
    }
}
