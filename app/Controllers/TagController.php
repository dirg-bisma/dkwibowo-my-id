<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Repositories\TagRepository;
use App\Services\SeoService;
use App\Support\Response;
use App\Support\View;

final class TagController
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly TagRepository $tags,
        private readonly View $view,
        private readonly int $perPage,
        private readonly int $maxPerPage,
        private readonly SeoService $seo,
    ) {
    }

    public function show(string $language, string $slug, int $page): never
    {
        $tag = $this->tags->findBySlug($slug);
        if ($tag === null) {
            Response::html($this->view->page('errors/404', ['title' => '404']), 404);
        }

        $page = max(1, $page);
        $perPage = min(max(1, $this->perPage), $this->maxPerPage);
        $total = $this->content->countPublished($slug);

        Response::html($this->view->page('tag/detail', [
            'language' => $language,
            'tag' => $tag,
            'items' => $this->content->listPublished($perPage, ($page - 1) * $perPage, $slug),
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'title' => $tag['name'],
            'seo' => $this->seo->generatePageMeta(
                $tag['name'],
                '/' . $language . '/tag/' . $tag['slug'],
                $tag['name'],
                [
                    'id' => $this->seo->url('/id/tag/' . $tag['slug']),
                    'en' => $this->seo->url('/en/tag/' . $tag['slug']),
                    'x-default' => $this->seo->url('/'),
                ]
            ),
        ]));
    }
}
