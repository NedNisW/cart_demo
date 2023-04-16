<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

use App\Cart\Entity\LineItem;
use App\Product\Serializer\ProductSerializer;
use Traversable;

class LineItemSerializer
{
    private LineItemSerializerConfig $lineItemSerializerConfig;

    public function __construct(
        private readonly ProductSerializer $productSerializer,
        ?LineItemSerializerConfig $lineItemSerializerConfig = null,
    ) {
        $this->lineItemSerializerConfig = $lineItemSerializerConfig ?? LineItemSerializerConfig::create();
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

    /**
     * @return array<string, mixed>
     */
    public function serialize(LineItem $lineItem): array
    {
        $data = $this->getBaseData($lineItem);

        if ($this->lineItemSerializerConfig->isWithCartReference()) {
            $data['cart_id'] = (string) $lineItem->getCart()->getId();
        }

        return $data;
    }

    /**
     * @param iterable<LineItem> $traversable
     * @return array<int, array<string, mixed>>
     */
    public function serializeBatch(iterable $traversable): array
    {
        $data = [];

        foreach ($traversable as $lineItem) {
            $data[] = $this->serialize($lineItem);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getBaseData(LineItem $lineItem): array
    {
        return [
            'id' => $lineItem->getId() ? (string) $lineItem->getId() : null,
            'product' => $this->productSerializer->serialize($lineItem->getProduct()),
            'quantity' => $lineItem->getQuantity(),
        ];
    }
}
