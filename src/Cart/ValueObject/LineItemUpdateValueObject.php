<?php
declare(strict_types=1);

namespace App\Cart\ValueObject;

use InvalidArgumentException;

class LineItemUpdateValueObject
{
    private function __construct(
        private ?int $newAmount = null
    ) {
    }

    /**
     * @return static
     */
    public static function create(): self
    {
        return new self();
    }

    public static function fromRequestPayload(array $requestData): self
    {
        $instance = new self();

        if (isset($requestData['amount'])) {
            if (!is_int($requestData['amount'])) {
                throw new InvalidArgumentException('amount must be integer value');
            }

            $instance->newAmount = $requestData['amount'];
        }

        return $instance;
    }

    public function withNewAmount(?int $newAmount): self
    {
        $new = clone $this;
        $new->newAmount = $newAmount;

        return $new;
    }

    /**
     * @return int|null
     */
    public function getNewAmount(): ?int
    {
        return $this->newAmount;
    }
}