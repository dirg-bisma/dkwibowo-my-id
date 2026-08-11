<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\SearchService;
use App\Support\Response;
use App\Support\View;

final class SearchController
{
    public function __construct(
        private readonly SearchService $search,
        private readonly View $view,
        private readonly int $perPage,
        private readonly int $maxPerPage,
    ) {
    }

    public function show(string $language, string $query, ?string $tag, int $page): never
    {
        $perPage = min(max(1, $this->perPage), $this->maxPerPage);
        $result = $this->search->search($query, $tag, $page, $perPage);

        Response::html($this->view->page('search', [
            'language' => $language,
            'query' => $query,
            'tag' => $tag,
            'items' => $result['items'],
            'page' => $result['page'],
            'totalPages' => max(1, (int) ceil($result['total'] / $perPage)),
            'title' => $language === 'id' ? 'Pencarian' : 'Search',
            'metaRobots' => 'noindex,follow',
        ]));
    }
}
