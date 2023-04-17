<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cart\Serializer;

use App\Cart\Entity\Cart;
use App\Cart\Serializer\CartSerializer;
use App\Cart\Serializer\CartSerializerConfig;
use App\Cart\Serializer\LineItemSerializer;
use App\Cart\Service\CartCalculationService;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CartSerializerTest extends TestCase
{
    private readonly CartCalculationService&MockObject $cartCalculationServiceMock;
    private readonly LineItemSerializer&MockObject $lineItemSerializerMock;
    private readonly CartSerializer $unit;

    protected function setUp(): void
    {
        $this->cartCalculationServiceMock = self::createMock(CartCalculationService::class);
        $this->lineItemSerializerMock = self::createMock(LineItemSerializer::class);

        $this->unit = new CartSerializer($this->cartCalculationServiceMock, $this->lineItemSerializerMock);
    }

    public function testItSerializesByDefaultWithoutLineItems(): void
    {
        $createdAt = new DateTimeImmutable();
        $updatedAt = $createdAt->add(new \DateInterval('P1D'));

        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getId')->willReturn(self::createConfiguredStub(Uuid::class, ['__toString' => 'AAA']));
        $cartMock->method('getCreatedAt')->willReturn($createdAt);
        $cartMock->method('getUpdatedAt')->willReturn($updatedAt);
        $cartMock->expects(self::never())->method('getLineItems');

        $this->cartCalculationServiceMock->method('getCartTotalInEuroCents')->with($cartMock)->willReturn(123456);

        self::assertSame(
            [
                'id' => 'AAA',
                'total_in_euro_cents' => 123456,
                'created_at' => $createdAt->getTimestamp(),
                'updated_at' => $updatedAt->getTimestamp(),
            ],
            $this->unit->serialize($cartMock)
        );
    }

    public function testItSerializesWithLineItems(): void
    {
        $lineItemsCollection = new ArrayCollection();

        $createdAt = new DateTimeImmutable();
        $updatedAt = $createdAt->add(new \DateInterval('P1D'));

        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getId')->willReturn(self::createConfiguredStub(Uuid::class, ['__toString' => 'AAA']));
        $cartMock->method('getCreatedAt')->willReturn($createdAt);
        $cartMock->method('getUpdatedAt')->willReturn($updatedAt);
        $cartMock->expects(self::once())->method('getLineItems')->willReturn($lineItemsCollection);

        $this->cartCalculationServiceMock->method('getCartTotalInEuroCents')->with($cartMock)->willReturn(123456);
        $this->lineItemSerializerMock
            ->method('serializeBatch')
            ->with($lineItemsCollection)
            ->willReturn(['SERIALIZED LINE ITEMS DATA']);

        $newUnit = $this->unit->withConfig(CartSerializerConfig::create(true));

        self::assertNotSame($this->unit, $newUnit);
        self::assertSame(
            [
                'id' => 'AAA',
                'total_in_euro_cents' => 123456,
                'created_at' => $createdAt->getTimestamp(),
                'updated_at' => $updatedAt->getTimestamp(),
                'line_items' => ['SERIALIZED LINE ITEMS DATA']
            ],
            $newUnit->serialize($cartMock)
        );
    }
}
