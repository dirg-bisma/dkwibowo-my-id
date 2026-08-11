<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Services\MarkdownService;
use App\Services\SeoService;
use App\Support\Response;
use App\Support\View;
use Throwable;

final class ContentController
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly MarkdownService $markdown,
        private readonly SeoService $seo,
        private readonly View $view,
    ) {
    }

    public function show(string $language, string $slug): never
    {
        $content = $this->content->findPublishedBySlug($slug);

        if ($content === null) {
            $this->notFound();
        }

        try {
            $body = $this->markdown->read($language, $slug);
        } catch (Throwable) {
            $this->notFound();
        }

        $title = $language === 'id' ? $content['title_id'] : $content['title_en'];
        Response::html($this->view->page('content/detail', [
            'language' => $language,
            'content' => $content,
            'contentTitle' => $title,
            'body' => $this->markdown->render($body),
            'title' => $title,
            'seo' => $this->seo->generateMeta($content, $language),
        ]));
    }

    private function notFound(): never
    {
        Response::html($this->view->page('errors/404', ['title' => '404']), 404);
    }
}
