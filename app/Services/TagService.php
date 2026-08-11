<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TagRepository;
use App\Support\ConflictException;
use App\Support\ValidationException;

final class TagService
{
    public function __construct(private readonly TagRepository $tags)
    {
    }

    public function create(string $name): int
    {
        [$name, $slug] = $this->normalize($name);
        if ($this->tags->findBySlug($slug) !== null) {
            throw new ConflictException('Tag slug already exists.');
        }
        return $this->tags->create($name, $slug, gmdate('Y-m-d H:i:s'));
    }

    public function update(int $id, string $name): void
    {
        [$name, $slug] = $this->normalize($name);
        $existing = $this->tags->findBySlug($slug);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            throw new ConflictException('Tag slug already exists.');
        }
        $this->tags->update($id, $name, $slug, gmdate('Y-m-d H:i:s'));
    }

    public function delete(int $id): void
    {
        if ($this->tags->hasContent($id)) {
            throw new ConflictException('Tag is still used by content.');
        }
        $this->tags->delete($id);
    }

    public function syncNames(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $slug = $this->slugify($name);
            $tag = $this->tags->findBySlug($slug);
            $ids[] = $tag === null
                ? $this->tags->create($name, $slug, gmdate('Y-m-d H:i:s'))
                : (int) $tag['id'];
        }
        return array_values(array_unique($ids));
    }

    private function normalize(string $name): array
    {
        $name = trim($name);
        $slug = $this->slugify($name);
        if ($name === '' || $slug === '') {
            throw new ValidationException('Tag name is required.');
        }
        return [$name, $slug];
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}
