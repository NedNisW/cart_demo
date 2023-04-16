<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

class LineItemSerializerConfig
{
    private function __construct(private readonly bool $withCartReference)
    {
    }

    public static function create(bool $withCartReference = true): self
    {
        return new self($withCartReference);
    }

    public function isWithCartReference(): bool
    {
        return $this->withCartReference;
    }
}
