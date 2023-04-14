<?php
declare(strict_types=1);

namespace App\Common\Uuid\Exception;

use InvalidArgumentException;
use Throwable;

class UuidInvalidException extends InvalidArgumentException
{
    public function __construct(public readonly string $uuid, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('"%s" is not a valid UUID.', $this->uuid),
            $code,
            $previous
        );
    }
}