<?php

declare(strict_types=1);

namespace App\Services;

final class SeoService
{
    public function __construct(
        private readonly string $appUrl,
        private readonly MarkdownService $markdown,
    ) {
    }

    public function generateMeta(array $content, string $language): array
    {
        $title = $language === 'id' ? $content['title_id'] : $content['title_en'];
        $body = $this->readBody($content, $language);
        $description = $this->markdown->extractDescription($body);
        $canonical = $this->url('/' . $language . '/content/' . $content['slug']);
        $image = $content['cover_image'] ?: $this->url('/assets/img/default-og.svg');

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'og_image' => str_starts_with((string) $image, 'http') ? $image : $this->url('/' . ltrim((string) $image, '/')),
            'alternates' => [
                'id' => $this->url('/id/content/' . $content['slug']),
                'en' => $this->url('/en/content/' . $content['slug']),
                'x-default' => $this->url('/'),
            ],
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => ($content['project_type'] ?? 'creative_work') === 'software_application'
                    ? 'SoftwareApplication'
                    : 'CreativeWork',
                'name' => $title,
                'description' => $description,
                'image' => str_starts_with((string) $image, 'http') ? $image : $this->url('/' . ltrim((string) $image, '/')),
                'datePublished' => $content['published_at'],
                'dateModified' => $content['updated_at'],
            ],
        ];
    }

    public function generatePageMeta(string $title, string $canonical, string $description = '', ?array $alternates = null): array
    {
        $canonical = $this->url($canonical);
        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'alternates' => $alternates ?? [
                'id' => $this->url('/id'),
                'en' => $this->url('/en'),
                'x-default' => $this->url('/'),
            ],
            'schema' => null,
        ];
    }

    private function readBody(array $content, string $language): string
    {
        $path = $language === 'id' ? $content['file_path_id'] : $content['file_path_en'];
        $body = is_string($path) ? file_get_contents(dirname(__DIR__, 2) . '/' . ltrim($path, '/')) : false;
        return $body === false ? '' : $body;
    }

    public function url(string $path): string
    {
        return rtrim($this->appUrl, '/') . '/' . ltrim($path, '/');
    }
}
