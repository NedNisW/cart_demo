<?php
declare(strict_types=1);

namespace App\Cart\Exception;

use Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

class CartNotFoundException extends Exception
{
    public function __construct(public readonly Uuid $cartId, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Cart with UUID "%s" not found.', $this->cartId),
            $code,
            $previous
        );
    }
}