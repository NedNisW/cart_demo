<?php

declare(strict_types=1);

namespace App\Product\Exception;

use Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

class ProductNotFoundException extends Exception
{
    public function __construct(public readonly Uuid $productId, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Product with ID "%s" not found.', $this->productId),
            $code,
            $previous
        );
    }
}
