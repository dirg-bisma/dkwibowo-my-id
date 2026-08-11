<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentRepository;

final class SearchService
{
    public function __construct(private readonly ContentRepository $content)
    {
    }

    public function search(string $query, ?string $tag, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $match = $this->toMatchQuery($query);
        $offset = ($page - 1) * $perPage;

        if ($match === '') {
            return ['items' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }

        return [
            'items' => $this->content->searchPublished($match, $perPage, $offset, $tag),
            'total' => $this->content->countSearchPublished($match, $tag),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    private function toMatchQuery(string $query): string
    {
        $tokens = preg_split('/[^\pL\pN]+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return implode(' AND ', array_map(
            static fn (string $token): string => '"' . str_replace('"', '""', $token) . '"*',
            $tokens
        ));
    }
}
