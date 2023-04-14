<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

class LineItemSerializerConfig
{
    private function __construct(private bool $withCartReference)
    {
    }

    public static function create(bool $withCartReference = false): self
    {
        return new self($withCartReference);
    }

    public function withCartReference(bool $flag): self
    {
        $new = clone $this;
        $new->withCartReference = $flag;

        return $new;
    }

    public function isWithCartReference(): bool
    {
        return $this->withCartReference;
    }
}
