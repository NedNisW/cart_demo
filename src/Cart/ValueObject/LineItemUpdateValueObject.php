<?php

declare(strict_types=1);

namespace App\Cart\ValueObject;

use InvalidArgumentException;

class LineItemUpdateValueObject
{
    private function __construct(
        private ?int $newQuantity = null
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<int|string, mixed> $requestData
     */
    public static function fromRequestPayload(array $requestData): self
    {
        $instance = new self();

        if (isset($requestData['quantity'])) {
            if (!is_int($requestData['quantity'])) {
                throw new InvalidArgumentException('quantity must be integer value');
            }

            $instance->newQuantity = $requestData['quantity'];
        }

        return $instance;
    }

    public function withNewQuantity(?int $newQuantity): self
    {
        $new = clone $this;
        $new->newQuantity = $newQuantity;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getNewQuantity(): ?int
    {
        return $this->newQuantity;
    }
}
