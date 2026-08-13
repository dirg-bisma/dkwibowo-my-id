<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\ValidationException;

final class MediaService
{
    private const MAX_SIZE = 2097152;
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
    ];

    public function __construct(private readonly string $root, private readonly int $maxSize = self::MAX_SIZE)
    {
    }

    public function uploadCover(array $file): string
    {
        return $this->store($file, 'cover');
    }

    public function uploadInline(array $file): string
    {
        return $this->store($file, 'inline', self::MAX_SIZE);
    }

    private function store(array $file, string $kind, ?int $maxSize = null): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_string($file['tmp_name'] ?? null)
            || !is_uploaded_file($file['tmp_name'])
            || (int) ($file['size'] ?? 0) > ($maxSize ?? $this->maxSize)
            || (int) ($file['size'] ?? 0) <= 0
        ) {
            throw new ValidationException('Invalid or oversized upload.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo === false ? false : finfo_file($finfo, $file['tmp_name']);
        if ($finfo !== false) {
            finfo_close($finfo);
        }
        $isRaster = @getimagesize($file['tmp_name']) !== false;
        $isSafeSvg = $mime === 'image/svg+xml' && $this->isSafeSvg($file['tmp_name']);
        if (!is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime]) || (!$isRaster && !$isSafeSvg)) {
            throw new ValidationException('Uploaded file is not a supported image.');
        }

        $directory = $this->root . '/storage/media/' . $kind;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new ValidationException('Unable to create media directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::MIME_EXTENSIONS[$mime];
        $path = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new ValidationException('Unable to store uploaded file.');
        }

        return 'storage/media/' . $kind . '/' . $filename;
    }

    private function isSafeSvg(string $path): bool
    {
        $svg = file_get_contents($path);
        if (!is_string($svg) || stripos($svg, '<svg') === false) {
            return false;
        }

        return preg_match('/<\s*script|on[a-z]+\s*=|javascript\s*:|<\s*iframe|<\s*object|<\s*embed/i', $svg) !== 1;
    }
}
