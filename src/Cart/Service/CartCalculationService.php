<?php

declare(strict_types=1);

namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;

class CartCalculationService
{
    public function getCartTotalInEuroCents(Cart $cart): int
    {
        return $cart->getLineItems()->reduce(
            function (int $carry, LineItem $lineItem) {
                return $carry + ($lineItem->getAmount() * $lineItem->getProduct()->getPriceInEuroCents());
            },
            0
        );
    }
}
