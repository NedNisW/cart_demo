<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cart\ValueObject;

use App\Cart\ValueObject\LineItemUpdateValueObject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LineItemUpdateValueObjectTest extends TestCase
{
    public function testFromRequestPayloadAppliesQuantity(): void
    {
        $requestPayload = ['some' => 'thing', 'quantity' => 123];

        $instance = LineItemUpdateValueObject::fromRequestPayload($requestPayload);
        self::assertSame(123, $instance->getNewQuantity());
    }

    public function testFromRequestPayloadThrowsExceptionOnInvalidQuantityValue(): void
    {
        self::expectException(InvalidArgumentException::class);
        $requestPayload = ['some' => 'thing', 'quantity' => 'abc'];

        $instance = LineItemUpdateValueObject::fromRequestPayload($requestPayload);
    }

    public function testNewQuantityIsStillNullIfNotPartOfRequestPayload(): void
    {
        $requestPayload = ['some' => 'thing'];

        $instance = LineItemUpdateValueObject::fromRequestPayload($requestPayload);
        self::assertNull($instance->getNewQuantity());
    }

    public function testWithNewQuantityIsImmutable(): void
    {
        $firstInstance = LineItemUpdateValueObject::create();
        $secondInstance = $firstInstance->withNewQuantity(123);

        self::assertNotSame($firstInstance, $secondInstance);
        self::assertNull($firstInstance->getNewQuantity());
        self::assertSame(123, $secondInstance->getNewQuantity());
    }
}
