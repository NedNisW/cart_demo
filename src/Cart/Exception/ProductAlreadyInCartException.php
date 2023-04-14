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
        public readonly int $sku,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('Product "%d" is already in the Cart "%s".', $this->cartId, $this->sku),
            $code,
            $previous
        );
    }
}
