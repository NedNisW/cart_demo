<?php

declare(strict_types=1);

namespace Tests\Unit\Cart\Serializer;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Serializer\LineItemSerializer;
use App\Cart\Serializer\LineItemSerializerConfig;
use App\Product\Entity\Product;
use App\Product\Serializer\ProductSerializer;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class LineItemSerializerTest extends TestCase
{
    private const CART_UUID = 'c1fbaf51-c517-4abe-9b34-6345a8e76b95';
    private const LINE_ITEM_UUID = 'e92cd118-6588-494c-9004-b2f79f6809f3';

    private readonly LineItemSerializer $unit;
    private readonly ProductSerializer&MockObject $productSerializerMock;

    protected function setUp(): void
    {
        $this->productSerializerMock = self::createMock(ProductSerializer::class);
        $this->unit = new LineItemSerializer($this->productSerializerMock);
    }

    public function testDefaultLineItemSerializerIsWithCartReference(): void
    {
        ['product' => $product, 'lineItem' => $lineItem] = $this->createLineItemAndProductStub();

        $this->productSerializerMock->method('serialize')->with($product)->willReturn(['PRODUCT_DATA']);

        $expectedData = [
            'id' => self::LINE_ITEM_UUID,
            'product' => ['PRODUCT_DATA'],
            'quantity' => 123,
            'cart_id' => self::CART_UUID
        ];

        self::assertSame($expectedData, $this->unit->serialize($lineItem));
    }

    public function testLineItemSerializerConfigOverride(): void
    {
        ['product' => $product, 'lineItem' => $lineItem] = $this->createLineItemAndProductStub();
        $config = LineItemSerializerConfig::create(false);
        $newUnit = $this->unit->withLineItemSerializerConfig($config);

        $this->productSerializerMock->method('serialize')->with($product)->willReturn(['PRODUCT_DATA']);

        $expectedData = [
            'id' => self::LINE_ITEM_UUID,
            'product' => ['PRODUCT_DATA'],
            'quantity' => 123,
        ];

        self::assertNotSame($this->unit, $newUnit); // Check for immutability
        self::assertSame($expectedData, $newUnit->serialize($lineItem));
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function testBatchSerialization(bool $withCartId): void
    {
        ['lineItem' => $lineItemOne, 'product' => $productOne] = $this->createLineItemAndProductStub('C1', 'LI1', 1);
        ['lineItem' => $lineItemTwo, 'product' => $productTwo] = $this->createLineItemAndProductStub('C2', 'LI2', 2);
        ['lineItem' => $lineItemThree, 'product' => $productThree] =
            $this->createLineItemAndProductStub('C3', 'LI3', 3);

        $this->productSerializerMock
            ->expects(self::exactly(3))
            ->method('serialize')
            ->willReturnMap([
                [$productOne, ['P1DATA']],
                [$productTwo, ['P2DATA']],
                [$productThree, ['P3DATA']],
            ]);

        $expectedItems = [
            $this->buildExpectedDataForLineItem('LI1', ['P1DATA'], 1, $withCartId ? 'C1' : false),
            $this->buildExpectedDataForLineItem('LI2', ['P2DATA'], 2, $withCartId ? 'C2' : false),
            $this->buildExpectedDataForLineItem('LI3', ['P3DATA'], 3, $withCartId ? 'C3' : false),
        ];

        self::assertSame(
            $expectedItems,
            $this->unit
                ->withLineItemSerializerConfig(LineItemSerializerConfig::create($withCartId))
                ->serializeBatch([$lineItemOne, $lineItemTwo, $lineItemThree])
        );
    }

    /**
     * @param array<string|int, mixed> $productData
     * @return array{id: string, product: array<string|int, mixed>, quantity: int, cart?: string}
     */
    private function buildExpectedDataForLineItem(string $id, array $productData, int $qty, string|false $cartId): array
    {
        $data = [
            'id' => $id,
            'product' => $productData,
            'quantity' => $qty,
        ];

        if (false !== $cartId) {
            $data['cart_id'] = $cartId;
        }

        return $data;
    }

    /**
     * @return array{
     *              cart: Cart&Stub,
     *              product: Product&Stub,
     *              lineItem: LineItem&Stub
     *         }
     */
    private function createLineItemAndProductStub(
        string $cartUuid = self::CART_UUID,
        string $lineItemUuid = self::LINE_ITEM_UUID,
        int $quantity = 123,
    ): array {
        $cartStub = self::createStub(Cart::class);
        $cartStub->method('getId')->willReturn(self::createConfiguredStub(Uuid::class, ['__toString' => $cartUuid]));

        $productStub = self::createStub(Product::class);

        $lineItemStub = self::createStub(LineItem::class);
        $lineItemStub->method('getCart')->willReturn($cartStub);
        $lineItemStub->method('getProduct')->willReturn($productStub);
        $lineItemStub->method('getQuantity')->willReturn($quantity);
        $lineItemStub
            ->method('getId')
            ->willReturn(self::createConfiguredStub(Uuid::class, ['__toString' => $lineItemUuid]));

        return [
            'cart' => $cartStub,
            'product' => $productStub,
            'lineItem' => $lineItemStub
        ];
    }
}
