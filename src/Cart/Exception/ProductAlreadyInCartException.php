<?php

declare(strict_types=1);

namespace App\Cart\Exception;

use Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

class ProductAlreadyInCartException extends Exception
{
    public function __construct(
        public readonly Uuid $cartId,
        public readonly Uuid $productId,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('Product "%s" is already in the Cart "%s".', $this->cartId, $this->productId),
            $code,
            $previous
        );
    }
}
