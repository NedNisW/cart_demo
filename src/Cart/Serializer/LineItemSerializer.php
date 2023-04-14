<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

use App\Cart\Entity\LineItem;
use App\Product\Serializer\ProductSerializer;
use Traversable;

class LineItemSerializer
{
    public function __construct(
        private ?LineItemSerializerConfig $lineItemSerializerConfig,
        private readonly ProductSerializer $productSerializer,
    ) {
        $this->lineItemSerializerConfig ??= LineItemSerializerConfig::create();
    }

    /**
     * @return static
     */
    public function withLineItemSerializerConfig(LineItemSerializerConfig $config): self
    {
        $new = clone $this;
        $new->lineItemSerializerConfig = $config;

        return $new;
    }

    public function serialize(LineItem $lineItem): array
    {
        $data = $this->getBaseData($lineItem);

        if ($this->lineItemSerializerConfig->isWithCartReference()) {
            $data['cart_uuid'] = (string) $lineItem->getCart()->getId();
        }

        return $data;
    }

    /**
     * @param Traversable<LineItem> $traversable
     * @return array
     */
    public function serializeAll(Traversable $traversable): array
    {
        $data = [];

        foreach ($traversable as $lineItem) {
            $data[] = $this->serialize($lineItem);
        }

        return $data;
    }

    private function getBaseData(LineItem $lineItem): array
    {
        return [
            'id' => $lineItem->getId(),
            'product' => $this->productSerializer->serialize($lineItem->getProduct()),
            'amount' => $lineItem->getAmount(),
        ];
    }
}
