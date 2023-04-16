<?php

declare(strict_types=1);

namespace Tests\Unit\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Service\CartCalculationService;
use App\Product\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Traversable;

class CartCalculationServiceTest extends TestCase
{
    private readonly CartCalculationService $unit;

    protected function setUp(): void
    {
        $this->unit = new CartCalculationService();
    }

    /**
     * @param array<int, array{pricePerUnit: int, qty: int}> $lineItemsConfig
     * @throws Exception
     */
    #[DataProvider('cartTotalCalculationTestDataProvider')]
    public function testCartTotalCalculation(array $lineItemsConfig, int $expectedPriceInEuroCents): void
    {
        $lineItemsCollection = new ArrayCollection();
        foreach ($lineItemsConfig as $lineItemConfig) {
            $productStub = self::createStub(Product::class);
            $productStub->method('getPriceInEuroCents')->willReturn($lineItemConfig['pricePerUnit']);

            $lineItemStub = self::createStub(LineItem::class);
            $lineItemStub->method('getProduct')->willReturn($productStub);
            $lineItemStub->method('getQuantity')->willReturn($lineItemConfig['qty']);

            $lineItemsCollection->add($lineItemStub);
        }

        $cart = self::createStub(Cart::class);
        $cart->method('getLineItems')->willReturn($lineItemsCollection);

        self::assertSame($expectedPriceInEuroCents, $this->unit->getCartTotalInEuroCents($cart));
    }

    /**
     * @return Traversable<array<string, mixed>>
     */
    public static function cartTotalCalculationTestDataProvider(): Traversable
    {
        yield 'Two Items with different qtys' => [
            'lineItemsConfig' => [
                ['qty' => 4, 'pricePerUnit' => 100],
                ['qty' => 2, 'pricePerUnit' => 50]
            ],
            'expectedPriceInEuroCents' => 500
        ];

        yield 'One Item, multiple qty' => [
            'lineItemsConfig' => [
                ['qty' => 4, 'pricePerUnit' => 100],
            ],
            'expectedPriceInEuroCents' => 400
        ];

        yield 'One Item, one qty' => [
            'lineItemsConfig' => [
                ['qty' => 1, 'pricePerUnit' => 100],
            ],
            'expectedPriceInEuroCents' => 100
        ];

        yield 'No items' => [
            'lineItemsConfig' => [],
            'expectedPriceInEuroCents' => 0
        ];
    }
}
