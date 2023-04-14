<?php

declare(strict_types=1);

namespace App\Cart\Serializer;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Service\CartCalculationService;

class CartSerializer
{
    public function __construct(
        private readonly CartCalculationService $cartCalculationService,
        private readonly LineItemSerializer $lineItemSerializer,
        private ?CartSerializerConfig $config
    ) {
        $this->config ??= CartSerializerConfig::create();
    }

    /**
     * @return static
     */
    public function withConfig(CartSerializerConfig $config): self
    {
        $new = clone $this;
        $new->config = $config;

        return $new;
    }

    public function serialize(Cart $cart): array
    {
        $data = $this->getBaseData($cart);

        if ($this->config->isWithLineItems()) {
            $data['lineItems'] = $cart->getLineItems()->map(
                fn (LineItem $item) => $this->lineItemSerializer->serialize($item)
            )->toArray();
        }

        return $data;
    }

    private function getBaseData(Cart $cart): array
    {
        return [
            'id' => $cart->getId(),
            'total_in_euro_cents' => $this->cartCalculationService->getCartTotalInEuroCents($cart),
            'created_at' => $cart->getCreatedAt()->getTimestamp(),
        ];
    }
}
