<?php

declare(strict_types=1);

namespace App\Cart\Exception;

use Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

class LineItemNotFoundException extends Exception
{
    public function __construct(public readonly Uuid $lineItemId, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Line Item with UUID "%s" not found.', $this->lineItemId),
            $code,
            $previous
        );
    }
}
