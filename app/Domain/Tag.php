<?php

declare(strict_types=1);

namespace App\Domain;

final readonly class Tag
{
    public function __construct(public array $attributes)
    {
    }

    public static function fromRow(array $row): self
    {
        return new self($row);
    }
}
