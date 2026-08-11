<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Services\SeoService;
use App\Support\Response;
use App\Support\View;

final class PublicController
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly View $view,
        private readonly int $perPage,
        private readonly int $maxPerPage,
        private readonly SeoService $seo,
    ) {
    }

    public function index(string $language, ?string $tag, int $page): never
    {
        $page = max(1, $page);
        $perPage = 5;
        $total = $this->content->countPublished($tag);
        $items = $this->content->listPublished($perPage, ($page - 1) * $perPage, $tag);

        Response::html($this->view->page('home', [
            'language' => $language,
            'items' => $items,
            'tag' => $tag,
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'copy' => $this->copy($language),
            'title' => 'dkwibowo.my.id',
            'seo' => $this->seo->generatePageMeta(
                'dkwibowo.my.id',
                '/' . $language . ($tag === null ? '' : '?tag=' . rawurlencode($tag)),
                $this->copy($language)['meta_description']
            ),
        ]));
    }

    public function projectArchive(string $language, int $page): never
    {
        $page = max(1, $page);
        $perPage = min(max(1, $this->perPage), $this->maxPerPage);
        $total = $this->content->countPublished();

        Response::html($this->view->page('project/archive', [
            'language' => $language,
            'items' => $this->content->listPublished($perPage, ($page - 1) * $perPage),
            'page' => $page,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
            'title' => $language === 'id' ? 'Project — dkwibowo.my.id' : 'Projects — dkwibowo.my.id',
            'seo' => $this->seo->generatePageMeta(
                $language === 'id' ? 'Project' : 'Projects',
                '/' . $language . '/project',
                $language === 'id' ? 'Kumpulan project dkwibowo.my.id.' : 'A collection of projects by dkwibowo.my.id.',
                [
                    'id' => $this->seo->url('/id/project'),
                    'en' => $this->seo->url('/en/project'),
                    'x-default' => $this->seo->url('/'),
                ]
            ),
        ]));
    }

    private function copy(string $language): array
    {
        if ($language === 'id') {
            return [
                'eyebrow' => 'FULL-STACK ENGINEER · AI',
                'heading' => 'Membangun sistem cerdas dengan AI dan engineering yang scalable',
                'body' => 'Saya merancang dan membangun produk digital yang andal, dari sistem data yang kompleks hingga pengalaman pengguna yang thoughtful.',
                'primary_cta' => 'Hubungi saya',
                'secondary_cta' => 'Lihat karya pilihan',
                'availability' => 'Terbuka untuk kolaborasi',
                'meta_description' => 'Portfolio bilingual dkwibowo.my.id tentang engineering, AI, dan project digital.',
            ];
        }

        return [
            'eyebrow' => 'FULL-STACK ENGINEER · AI',
            'heading' => 'Building intelligent systems with AI and scalable engineering',
            'body' => 'I design and build reliable digital products, from complex data systems to thoughtful user experiences.',
            'primary_cta' => 'Get in touch',
            'secondary_cta' => 'View selected work',
            'availability' => 'Open for collaboration',
            'meta_description' => 'A bilingual portfolio by dkwibowo.my.id covering engineering, AI, and digital projects.',
        ];
    }
}
