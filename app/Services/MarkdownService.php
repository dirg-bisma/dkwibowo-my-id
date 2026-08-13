<?php

declare(strict_types=1);

namespace App\Services;

use League\CommonMark\Extension\Table\TableExtension;
use App\Support\Slug;
use RuntimeException;

final class MarkdownService
{
    public function __construct(private readonly string $root)
    {
    }

    public function read(string $language, string $slug): string
    {
        if (!in_array($language, ['id', 'en'], true)) {
            throw new RuntimeException('Unsupported language.');
        }

        if (!Slug::isValid($slug)) {
            throw new RuntimeException('Invalid content slug.');
        }

        $path = $this->root . '/content/' . $language . '/' . $slug . '.md';

        if (!is_file($path)) {
            throw new RuntimeException('Markdown content not found.');
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException('Unable to read Markdown content.');
        }

        return $content;
    }

    public function render(string $markdown): string
    {
        if (!class_exists(\League\CommonMark\CommonMarkConverter::class) || !class_exists(\HTMLPurifier::class)) {
            throw new RuntimeException('Markdown dependencies are not installed.');
        }

        $converter = new \League\CommonMark\CommonMarkConverter();
        $converter->getEnvironment()->addExtension(new TableExtension());
        $html = $converter->convert($markdown)->getContent();
        $config = \HTMLPurifier_Config::createDefault();
        $cachePath = $this->root . '/storage/cache/htmlpurifier';
        if (!is_dir($cachePath) && !mkdir($cachePath, 0775, true) && !is_dir($cachePath)) {
            throw new RuntimeException('Unable to create HTML Purifier cache directory.');
        }
        if (!is_writable($cachePath)) {
            $cachePath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dkwibowo-htmlpurifier';
            if (!is_dir($cachePath) && !mkdir($cachePath, 0775, true) && !is_dir($cachePath)) {
                throw new RuntimeException('Unable to create temporary HTML Purifier cache directory.');
            }
        }
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set(
            'HTML.Allowed',
            'a[href|title|rel],p,br,blockquote,code,pre,em,strong,del,'
            . 'h1,h2,h3,h4,h5,h6,ul,ol,li,table,thead,tbody,tr,th,td,'
            . 'img[src|alt|title|width|height]'
        );

        return (new \HTMLPurifier($config))->purify($html);
    }

    public function extractDescription(string $markdown): string
    {
        $html = $this->render($markdown);
        $html = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/is', '', $html) ?? $html;
        $text = strip_tags($html);
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);
        if ((function_exists('mb_strlen') && mb_strlen($text) <= 160) || (!function_exists('mb_strlen') && strlen($text) <= 160)) {
            return $text;
        }

        $short = function_exists('mb_substr') ? mb_substr($text, 0, 160) : substr($text, 0, 160);
        $boundary = strrpos($short, ' ');
        return rtrim($boundary === false ? $short : substr($short, 0, $boundary)) . '…';
    }
}
