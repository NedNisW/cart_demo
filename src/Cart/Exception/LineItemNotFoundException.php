<?php

declare(strict_types=1);

namespace App\Cart\Exception;

use Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

class LineItemNotFoundException extends Exception
{
    public function __construct(string $message, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
