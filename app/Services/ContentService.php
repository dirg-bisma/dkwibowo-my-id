<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentRepository;
use App\Repositories\RedirectRepository;
use App\Support\ConflictException;
use App\Support\Slug;
use App\Support\ValidationException;
use SQLite3;
use Throwable;

final class ContentService
{
    public function __construct(
        private readonly SQLite3 $db,
        private readonly string $root,
        private readonly ContentRepository $content,
        private readonly RedirectRepository $redirects,
        private readonly TagService $tags,
    ) {
    }

    public function createDraft(array $data): int
    {
        $data = $this->validatedData($data);
        if ($this->content->slugExists($data['slug'])) {
            throw new ConflictException('Slug already exists.');
        }

        $paths = $this->paths($data['slug']);
        $this->writeMarkdown($paths, $data['content_id'], $data['content_en']);
        $now = gmdate('Y-m-d H:i:s');
        $this->db->exec('BEGIN');
        try {
            $id = $this->content->create([
                'slug' => $data['slug'],
                'project_type' => $data['project_type'],
                'title_id' => $data['title_id'],
                'title_en' => $data['title_en'],
                'file_path_id' => $paths['id'],
                'file_path_en' => $paths['en'],
                'cover_image' => $data['cover_image'] ?: null,
                'status' => 'draft',
                'published_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->content->replaceTags($id, $this->tags->syncNames($data['tags']));
            $this->db->exec('COMMIT');
            return $id;
        } catch (Throwable $exception) {
            $this->db->exec('ROLLBACK');
            $this->removePaths($paths);
            throw $exception;
        }
    }

    public function update(int $id, array $data): void
    {
        $current = $this->content->findById($id);
        if ($current === null) {
            throw new ValidationException('Content not found.');
        }
        if ($current['status'] === 'trashed') {
            throw new ValidationException('Trashed content must be restored before editing.');
        }
        $data = $this->validatedData($data);
        if ($this->content->slugExists($data['slug'], $id)) {
            throw new ConflictException('Slug already exists.');
        }

        $paths = $this->paths($data['slug']);
        $this->writeMarkdown($paths, $data['content_id'], $data['content_en']);
        $now = gmdate('Y-m-d H:i:s');
        try {
            $this->db->exec('BEGIN');
            $this->content->update($id, [
                ...$data,
                'file_path_id' => $paths['id'],
                'file_path_en' => $paths['en'],
                'updated_at' => $now,
            ]);
            $this->content->replaceTags($id, $this->tags->syncNames($data['tags']));
            if ($current['slug'] !== $data['slug']) {
                $this->redirects->create($id, '/id/content/' . $current['slug'], '/id/content/' . $data['slug'], $now);
                $this->redirects->create($id, '/en/content/' . $current['slug'], '/en/content/' . $data['slug'], $now);
            }
            $this->content->rebuildSearchIndex();
            $this->db->exec('COMMIT');
            if ($current['slug'] !== $data['slug']) {
                $this->removePaths(['id' => $current['file_path_id'], 'en' => $current['file_path_en']]);
            }
        } catch (Throwable $exception) {
            $this->db->exec('ROLLBACK');
            $this->removePaths($paths);
            throw $exception;
        }
    }

    public function publish(int $id): void
    {
        $content = $this->requiredContent($id);
        if ($content['status'] !== 'draft') {
            throw new ValidationException('Only draft content can be published.');
        }
        $this->assertPublishable($content);
        $this->setStatus($id, 'published', gmdate('Y-m-d H:i:s'));
    }

    public function archive(int $id): void
    {
        $content = $this->requiredContent($id);
        if ($content['status'] !== 'published') {
            throw new ValidationException('Only published content can be archived.');
        }
        $this->setStatus($id, 'archived', $content['published_at']);
    }

    public function restoreAsDraft(int $id): void
    {
        $content = $this->requiredContent($id);
        if ($content['status'] !== 'archived') {
            throw new ValidationException('Only archived content can be restored.');
        }
        $this->setStatus($id, 'draft', $content['published_at']);
    }

    public function restoreAsPublished(int $id): void
    {
        $content = $this->requiredContent($id);
        $this->assertPublishable($content);
        $this->setStatus($id, 'published', gmdate('Y-m-d H:i:s'));
    }

    private function setStatus(int $id, string $status, ?string $publishedAt): void
    {
        $this->content->setStatus($id, $status, $publishedAt, gmdate('Y-m-d H:i:s'));
        $this->content->rebuildSearchIndex();
    }

    private function requiredContent(int $id): array
    {
        $content = $this->content->findById($id);
        if ($content === null) {
            throw new ValidationException('Content not found.');
        }
        return $content;
    }

    private function assertPublishable(array $content): void
    {
        foreach (['slug', 'title_id', 'title_en', 'cover_image'] as $field) {
            if (trim((string) ($content[$field] ?? '')) === '') {
                throw new ValidationException('Content is not ready to publish.');
            }
        }
        foreach (['file_path_id', 'file_path_en'] as $field) {
            if (!is_file($this->root . '/' . $content[$field]) || filesize($this->root . '/' . $content[$field]) === 0) {
                throw new ValidationException('Both Markdown files are required.');
            }
        }
    }

    private function validatedData(array $data): array
    {
        $slug = strtolower(trim((string) ($data['slug'] ?? '')));
        Slug::assertValid($slug);
        return [
            'slug' => $slug,
            'project_type' => in_array(($data['project_type'] ?? 'creative_work'), ['creative_work', 'software_application'], true)
                ? $data['project_type']
                : 'creative_work',
            'title_id' => trim((string) ($data['title_id'] ?? '')),
            'title_en' => trim((string) ($data['title_en'] ?? '')),
            'content_id' => (string) ($data['content_id'] ?? ''),
            'content_en' => (string) ($data['content_en'] ?? ''),
            'cover_image' => trim((string) ($data['cover_image'] ?? '')),
            'tags' => is_array($data['tags'] ?? null) ? $data['tags'] : preg_split('/\s*,\s*/', (string) ($data['tags'] ?? ''), -1, PREG_SPLIT_NO_EMPTY),
        ];
    }

    private function paths(string $slug): array
    {
        return [
            'id' => 'content/id/' . $slug . '.md',
            'en' => 'content/en/' . $slug . '.md',
        ];
    }

    private function writeMarkdown(array $paths, string $id, string $en): void
    {
        foreach ([$paths['id'] => $id, $paths['en'] => $en] as $relative => $contents) {
            $path = $this->root . '/' . $relative;
            $directory = dirname($path);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new ValidationException('Unable to create content directory.');
            }
            $temporary = tempnam($directory, '.content-');
            if ($temporary === false || file_put_contents($temporary, $contents) === false || !rename($temporary, $path)) {
                if (is_string($temporary) && is_file($temporary)) {
                    unlink($temporary);
                }
                throw new ValidationException('Unable to write Markdown content.');
            }
        }
    }

    private function removePaths(array $paths): void
    {
        foreach ($paths as $relative) {
            $path = $this->root . '/' . $relative;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
