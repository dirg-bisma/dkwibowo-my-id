<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentRepository;
use App\Repositories\RedirectRepository;
use App\Repositories\TrashRepository;
use App\Support\ConflictException;
use App\Support\ValidationException;
use SQLite3;
use Throwable;

final class TrashService
{
    public function __construct(
        private readonly SQLite3 $db,
        private readonly string $root,
        private readonly ContentRepository $content,
        private readonly TrashRepository $trash,
        private readonly RedirectRepository $redirects,
    ) {
    }

    public function moveToTrash(int $contentId): void
    {
        $content = $this->requiredContent($contentId);
        if (!in_array($content['status'], ['draft', 'published', 'archived'], true)) {
            throw new ValidationException('Only active content can be moved to trash.');
        }

        $this->db->exec('BEGIN');
        $trashId = 0;
        $staging = null;
        $final = null;
        $moved = [];
        $movedInContainer = [];

        try {
            $trashId = $this->trash->create($contentId, $content['status'], $content['slug'], gmdate('Y-m-d H:i:s'));
            $staging = $this->trashRoot('.staging-' . $trashId);
            $final = $this->trashRoot((string) $trashId);
            $moved = $this->moveResources($content, $staging);
            $this->content->setStatus($contentId, 'trashed', $content['published_at'], gmdate('Y-m-d H:i:s'));
            if (!rename($staging, $final)) {
                throw new ValidationException('Unable to finalize trash operation.');
            }
            $movedInContainer = array_map(
                static fn (array $pair): array => [$pair[0], str_replace($staging . '/', $final . '/', $pair[1])],
                $moved
            );
            $this->content->rebuildSearchIndex();
            $this->db->exec('COMMIT');
        } catch (Throwable $exception) {
            $this->db->exec('ROLLBACK');
            if (is_string($final) && is_dir($final)) {
                $this->restoreMoved($final, $movedInContainer);
                $this->removeDirectory($final);
            }
            if (is_string($staging) && is_dir($staging)) {
                $this->restoreMoved($staging, $moved);
                $this->removeDirectory($staging);
            }
            throw $exception;
        }
    }

    public function restore(int $trashId): void
    {
        $trash = $this->trash->findById($trashId);
        if ($trash === null) {
            throw new ValidationException('Trash item not found.');
        }
        $content = $this->requiredContent((int) $trash['content_id']);
        if ($content['status'] !== 'trashed' || $this->content->slugExists($content['slug'], (int) $content['id'])) {
            throw new ConflictException('Slug conflict while restoring content.');
        }

        $container = $this->trashRoot((string) $trashId);
        if (!is_dir($container)) {
            throw new ValidationException('Trash files are missing.');
        }
        $resources = $this->resourceMapFromTrash($content, $container);
        $moved = [];

        $this->db->exec('BEGIN');
        try {
            $moved = $this->moveResourcesFromTrash($resources, $container);
            $this->content->setStatus((int) $content['id'], $trash['original_status'], $content['published_at'], gmdate('Y-m-d H:i:s'));
            $this->trash->delete($trashId);
            $this->content->rebuildSearchIndex();
            $this->db->exec('COMMIT');
            $this->removeDirectory($container);
        } catch (Throwable $exception) {
            $this->db->exec('ROLLBACK');
            $this->restoreMoved($this->trashRoot((string) $trashId), $moved);
            throw $exception;
        }
    }

    public function permanentDelete(int $trashId): void
    {
        $trash = $this->trash->findById($trashId);
        if ($trash === null) {
            throw new ValidationException('Trash item not found.');
        }
        $content = $this->requiredContent((int) $trash['content_id']);
        if ($content['status'] !== 'trashed') {
            throw new ValidationException('Permanent delete is only allowed from trash.');
        }

        $container = $this->trashRoot((string) $trashId);
        if (is_dir($container)) {
            $this->removeDirectory($container);
        }

        $this->db->exec('BEGIN');
        try {
            $this->redirects->deactivateForContent((int) $content['id']);
            $this->content->delete((int) $content['id']);
            $this->trash->delete($trashId);
            $this->content->rebuildSearchIndex();
            $this->db->exec('COMMIT');
        } catch (Throwable $exception) {
            $this->db->exec('ROLLBACK');
            throw $exception;
        }
    }

    private function requiredContent(int $id): array
    {
        $content = $this->content->findById($id);
        if ($content === null) {
            throw new ValidationException('Content not found.');
        }
        return $content;
    }

    private function moveResources(array $content, string $staging): array
    {
        $resources = [];
        foreach (['file_path_id' => 'id', 'file_path_en' => 'en'] as $field => $language) {
            $resources[(string) $content[$field]] = 'content/' . $language . '/' . basename((string) $content[$field]);
        }
        foreach ($this->mediaPaths($content) as $path) {
            $resources[$path] = 'media/' . substr($path, strlen('storage/media/'));
        }

        $moved = [];
        foreach ($resources as $source => $target) {
            $sourcePath = $this->safePath($source);
            if (!is_file($sourcePath)) {
                if (str_starts_with($source, 'storage/media/')) {
                    continue;
                }
                throw new ValidationException('Content file is missing.');
            }
            $targetPath = $staging . '/' . $target;
            $this->move($sourcePath, $targetPath);
            $moved[] = [$sourcePath, $targetPath];
        }
        return $moved;
    }

    private function resourceMapFromTrash(array $content, string $container): array
    {
        $resources = [];
        $resources[$container . '/content/id/' . basename((string) $content['file_path_id'])] = (string) $content['file_path_id'];
        $resources[$container . '/content/en/' . basename((string) $content['file_path_en'])] = (string) $content['file_path_en'];
        foreach ($this->mediaPathsFromTrash($content, $container) as $source => $target) {
            $resources[$source] = $target;
        }
        return $resources;
    }

    private function moveResourcesFromTrash(array $resources, string $container): array
    {
        $moved = [];
        foreach ($resources as $source => $target) {
            if (!is_file($source)) {
                throw new ValidationException('Trash file is missing.');
            }
            $targetPath = $this->safePath($target);
            if (file_exists($targetPath)) {
                throw new ConflictException('Restore target already exists.');
            }
            $this->move($source, $targetPath);
            $moved[] = [$targetPath, $source];
        }
        return $moved;
    }

    private function mediaPaths(array $content): array
    {
        $paths = [];
        if (is_string($content['cover_image'] ?? null) && str_starts_with($content['cover_image'], 'storage/media/')) {
            $paths[] = $content['cover_image'];
        }
        foreach (['file_path_id', 'file_path_en'] as $field) {
            $markdown = @file_get_contents($this->safePath((string) $content[$field])) ?: '';
            preg_match_all('~/?(storage/media/(?:cover|inline)/[A-Za-z0-9._-]+)~', $markdown, $matches);
            $paths = array_merge($paths, $matches[1] ?? []);
        }
        return array_values(array_unique($paths));
    }

    private function mediaPathsFromTrash(array $content, string $container): array
    {
        $paths = [];
        $markdownFiles = [
            $container . '/content/id/' . basename((string) $content['file_path_id']),
            $container . '/content/en/' . basename((string) $content['file_path_en']),
        ];
        foreach ($markdownFiles as $file) {
            $markdown = @file_get_contents($file) ?: '';
            preg_match_all('~/?(storage/media/(?:cover|inline)/[A-Za-z0-9._-]+)~', $markdown, $matches);
            foreach (array_unique($matches[1] ?? []) as $path) {
                $paths[$container . '/media/' . substr($path, strlen('storage/media/'))] = $path;
            }
        }
        if (is_string($content['cover_image'] ?? null) && str_starts_with($content['cover_image'], 'storage/media/')) {
            $paths[$container . '/media/' . substr($content['cover_image'], strlen('storage/media/'))] = $content['cover_image'];
        }
        return $paths;
    }

    private function safePath(string $relative): string
    {
        if (!str_starts_with($relative, 'content/') && !str_starts_with($relative, 'storage/media/')) {
            throw new ValidationException('Invalid storage path.');
        }
        if (str_contains($relative, '..') || str_contains($relative, '\\')) {
            throw new ValidationException('Invalid storage path.');
        }
        return $this->root . '/' . $relative;
    }

    private function trashRoot(string $name): string
    {
        if (!preg_match('/^(?:\d+|\.staging-\d+)$/', $name)) {
            throw new ValidationException('Invalid trash identifier.');
        }
        $root = $this->root . '/storage/trash/' . $name;
        if (!is_dir(dirname($root)) && !mkdir(dirname($root), 0775, true) && !is_dir(dirname($root))) {
            throw new ValidationException('Unable to create trash directory.');
        }
        return $root;
    }

    private function move(string $source, string $target): void
    {
        $directory = dirname($target);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new ValidationException('Unable to create staging directory.');
        }
        if (!rename($source, $target)) {
            throw new ValidationException('Unable to move storage resource.');
        }
    }

    private function restoreMoved(string $container, array $moved): void
    {
        foreach (array_reverse($moved) as [$from, $to]) {
            if (is_file($from)) {
                $directory = dirname($to);
                if (!is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }
                @rename($from, $to);
            }
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($directory);
    }
}
