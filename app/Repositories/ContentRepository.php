<?php

declare(strict_types=1);

namespace App\Repositories;

use SQLite3;
use SQLite3Result;

final class ContentRepository
{
    public function __construct(private readonly SQLite3 $db)
    {
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM content WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        return $this->fetchOne($statement->execute());
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM content WHERE slug = :slug LIMIT 1');
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        return $this->fetchOne($statement->execute());
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = $exceptId === null
            ? 'SELECT 1 FROM content WHERE slug = :slug LIMIT 1'
            : 'SELECT 1 FROM content WHERE slug = :slug AND id != :id LIMIT 1';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        if ($exceptId !== null) {
            $statement->bindValue(':id', $exceptId, SQLITE3_INTEGER);
        }
        return $statement->execute()->fetchArray(SQLITE3_NUM) !== false;
    }

    public function listAdmin(): array
    {
        return $this->fetchAll($this->db->query('SELECT * FROM content ORDER BY updated_at DESC, id DESC'));
    }

    public function listPublishedForSitemap(): array
    {
        return $this->fetchAll($this->db->query(
            "SELECT slug, updated_at FROM content WHERE status = 'published' ORDER BY updated_at DESC"
        ));
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare("SELECT * FROM content WHERE slug = :slug AND status = 'published' LIMIT 1");
        $statement->bindValue(':slug', $slug, SQLITE3_TEXT);
        return $this->fetchOne($statement->execute());
    }

    public function listPublished(int $limit, int $offset, ?string $tag = null): array
    {
        if ($tag === null || $tag === '') {
            $statement = $this->db->prepare(
                "SELECT * FROM content WHERE status = 'published' ORDER BY published_at DESC, id DESC LIMIT :limit OFFSET :offset"
            );
        } else {
            $statement = $this->db->prepare(<<<'SQL'
                SELECT c.*
                FROM content c
                JOIN content_tags ct ON ct.content_id = c.id
                JOIN tags t ON t.id = ct.tag_id
                WHERE c.status = 'published' AND t.slug = :tag
                ORDER BY c.published_at DESC, c.id DESC
                LIMIT :limit OFFSET :offset
            SQL);
            $statement->bindValue(':tag', $tag, SQLITE3_TEXT);
        }

        $statement->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $statement->bindValue(':offset', $offset, SQLITE3_INTEGER);
        return $this->fetchAll($statement->execute());
    }

    public function countPublished(?string $tag = null): int
    {
        if ($tag === null || $tag === '') {
            $statement = $this->db->prepare("SELECT COUNT(*) FROM content WHERE status = 'published'");
        } else {
            $statement = $this->db->prepare(<<<'SQL'
                SELECT COUNT(*)
                FROM content c
                JOIN content_tags ct ON ct.content_id = c.id
                JOIN tags t ON t.id = ct.tag_id
                WHERE c.status = 'published' AND t.slug = :tag
            SQL);
            $statement->bindValue(':tag', $tag, SQLITE3_TEXT);
        }

        return (int) $statement->execute()->fetchArray(SQLITE3_NUM)[0];
    }

    public function searchPublished(string $query, int $limit, int $offset, ?string $tag = null): array
    {
        $statement = $this->db->prepare(<<<'SQL'
            SELECT c.*
            FROM content_search s
            JOIN content c ON c.id = s.content_id
            WHERE content_search MATCH :query
              AND c.status = 'published'
              AND (:tag = '' OR EXISTS (
                  SELECT 1
                  FROM content_tags ct
                  JOIN tags t ON t.id = ct.tag_id
                  WHERE ct.content_id = c.id AND t.slug = :tag
              ))
            ORDER BY c.published_at DESC, c.id DESC
            LIMIT :limit OFFSET :offset
        SQL);
        $statement->bindValue(':query', $query, SQLITE3_TEXT);
        $statement->bindValue(':tag', $tag ?? '', SQLITE3_TEXT);
        $statement->bindValue(':limit', $limit, SQLITE3_INTEGER);
        $statement->bindValue(':offset', $offset, SQLITE3_INTEGER);
        return $this->fetchAll($statement->execute());
    }

    public function countSearchPublished(string $query, ?string $tag = null): int
    {
        $statement = $this->db->prepare(<<<'SQL'
            SELECT COUNT(*)
            FROM content_search s
            JOIN content c ON c.id = s.content_id
            WHERE content_search MATCH :query
              AND c.status = 'published'
              AND (:tag = '' OR EXISTS (
                  SELECT 1
                  FROM content_tags ct
                  JOIN tags t ON t.id = ct.tag_id
                  WHERE ct.content_id = c.id AND t.slug = :tag
              ))
        SQL);
        $statement->bindValue(':query', $query, SQLITE3_TEXT);
        $statement->bindValue(':tag', $tag ?? '', SQLITE3_TEXT);
        return (int) $statement->execute()->fetchArray(SQLITE3_NUM)[0];
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(<<<'SQL'
            INSERT INTO content (
                slug, project_type, title_id, title_en, file_path_id, file_path_en,
                cover_image, status, published_at, created_at, updated_at
            ) VALUES (
                :slug, :project_type, :title_id, :title_en, :file_path_id, :file_path_en,
                :cover_image, :status, :published_at, :created_at, :updated_at
            )
        SQL);
        foreach ($data as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->execute();
        return $this->db->lastInsertRowID();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare(<<<'SQL'
            UPDATE content
            SET slug = :slug,
                project_type = :project_type,
                title_id = :title_id,
                title_en = :title_en,
                file_path_id = :file_path_id,
                file_path_en = :file_path_en,
                cover_image = :cover_image,
                updated_at = :updated_at
            WHERE id = :id
        SQL);
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        foreach (['slug', 'project_type', 'title_id', 'title_en', 'file_path_id', 'file_path_en', 'cover_image', 'updated_at'] as $key) {
            $statement->bindValue(':' . $key, $data[$key] ?? null);
        }
        $statement->execute();
    }

    public function setStatus(int $id, string $status, ?string $publishedAt, string $updatedAt): void
    {
        $statement = $this->db->prepare(
            'UPDATE content SET status = :status, published_at = :published_at, updated_at = :updated_at WHERE id = :id'
        );
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->bindValue(':status', $status, SQLITE3_TEXT);
        $statement->bindValue(':published_at', $publishedAt);
        $statement->bindValue(':updated_at', $updatedAt, SQLITE3_TEXT);
        $statement->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM content WHERE id = :id');
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function replaceTags(int $contentId, array $tagIds): void
    {
        $statement = $this->db->prepare('DELETE FROM content_tags WHERE content_id = :content_id');
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $statement->execute();

        $statement = $this->db->prepare('INSERT INTO content_tags (content_id, tag_id) VALUES (:content_id, :tag_id)');
        foreach (array_unique(array_map('intval', $tagIds)) as $tagId) {
            $statement->reset();
            $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
            $statement->bindValue(':tag_id', $tagId, SQLITE3_INTEGER);
            $statement->execute();
        }
    }

    public function tagIds(int $contentId): array
    {
        $statement = $this->db->prepare('SELECT tag_id FROM content_tags WHERE content_id = :content_id ORDER BY tag_id');
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $result = $statement->execute();
        $ids = [];
        while (($row = $result->fetchArray(SQLITE3_NUM)) !== false) {
            $ids[] = (int) $row[0];
        }
        return $ids;
    }

    public function tagNames(int $contentId): array
    {
        $statement = $this->db->prepare(<<<'SQL'
            SELECT t.name
            FROM tags t
            JOIN content_tags ct ON ct.tag_id = t.id
            WHERE ct.content_id = :content_id
            ORDER BY t.name COLLATE NOCASE
        SQL);
        $statement->bindValue(':content_id', $contentId, SQLITE3_INTEGER);
        $result = $statement->execute();
        $names = [];
        while (($row = $result->fetchArray(SQLITE3_NUM)) !== false) {
            $names[] = $row[0];
        }
        return $names;
    }

    public function rebuildSearchIndex(): void
    {
        $this->db->exec('DELETE FROM content_search');
        $result = $this->db->query(<<<'SQL'
            SELECT c.id, c.title_id, c.title_en,
                   COALESCE(GROUP_CONCAT(t.name, ' '), '') AS tags
            FROM content c
            LEFT JOIN content_tags ct ON ct.content_id = c.id
            LEFT JOIN tags t ON t.id = ct.tag_id
            WHERE c.status = 'published'
            GROUP BY c.id
        SQL);

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $statement = $this->db->prepare(
                'INSERT INTO content_search (title_id, title_en, tags, content_id) VALUES (:title_id, :title_en, :tags, :content_id)'
            );
            $statement->bindValue(':title_id', $row['title_id'], SQLITE3_TEXT);
            $statement->bindValue(':title_en', $row['title_en'], SQLITE3_TEXT);
            $statement->bindValue(':tags', $row['tags'], SQLITE3_TEXT);
            $statement->bindValue(':content_id', $row['id'], SQLITE3_INTEGER);
            $statement->execute();
        }
    }

    private function fetchOne(SQLite3Result $result): ?array
    {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row === false ? null : $row;
    }

    private function fetchAll(SQLite3Result $result): array
    {
        $rows = [];
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $rows[] = $row;
        }
        return $rows;
    }
}
