<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Repositories\TagRepository;
use App\Support\Response;

final class DiscoveryController
{
    public function __construct(
        private readonly string $appUrl,
        private readonly ContentRepository $content,
        private readonly TagRepository $tags,
    ) {
    }

    public function sitemap(): never
    {
        $urls = [];
        foreach ($this->content->listPublishedForSitemap() as $item) {
            foreach (['id', 'en'] as $language) {
                $urls[] = [
                    'loc' => $this->url('/' . $language . '/content/' . $item['slug']),
                    'lastmod' => $item['updated_at'],
                ];
            }
        }
        foreach ($this->tags->listPublishedSlugs() as $slug) {
            foreach (['id', 'en'] as $language) {
                $urls[] = ['loc' => $this->url('/' . $language . '/tag/' . $slug)];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $item) {
            $xml .= '<url><loc>' . htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8') . '</loc>';
            if (isset($item['lastmod'])) {
                $xml .= '<lastmod>' . htmlspecialchars($item['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>';
            }
            $xml .= '</url>';
        }
        $xml .= '</urlset>';
        Response::xml($xml);
    }

    public function robots(): never
    {
        Response::text(
            "User-agent: *\nAllow: /\n\nDisallow: /admin/\nDisallow: /database/\nDisallow: /storage/\nDisallow: /content/\nSitemap: " . $this->url('/sitemap.xml') . "\n"
        );
    }

    private function url(string $path): string
    {
        return rtrim($this->appUrl, '/') . '/' . ltrim($path, '/');
    }
}
