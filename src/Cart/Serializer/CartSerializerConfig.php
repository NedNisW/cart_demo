<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

class CartSerializerConfig
{
    private function __construct(
        private bool $withLineItems = false
    ) {
    }

    public static function create(bool $withLineItems = false): self
    {
        return new self($withLineItems);
    }

    public function isWithLineItems(): bool
    {
        return $this->withLineItems;
    }
}
