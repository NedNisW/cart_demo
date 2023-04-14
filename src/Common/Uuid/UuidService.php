<?php

declare(strict_types=1);

namespace App\Common\Uuid;

use App\Common\Uuid\Exception\UuidInvalidException;
use Symfony\Component\Uid\Uuid;

class UuidService
{
    public function checkString(string $uuid): void
    {
        if (!Uuid::isValid($uuid)) {
            throw new UuidInvalidException($uuid);
        }
    }

    public function toUuid(string $uuid): Uuid
    {
        $this->checkString($uuid);

        return Uuid::fromString($uuid);
    }
}
