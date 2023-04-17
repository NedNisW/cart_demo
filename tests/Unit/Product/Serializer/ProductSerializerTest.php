<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Serializer;

use App\Product\Entity\Product;
use App\Product\Serializer\ProductSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Traversable;

class ProductSerializerTest extends TestCase
{
    private const UUID = 'd58c70a2-0567-4144-ad74-82fd77abe451';

    private readonly ProductSerializer $unit;

    protected function setUp(): void
    {
        $this->unit = new ProductSerializer();
    }

    public function testSerializeBatch(): void
    {
        $productStubOne = self::createConfiguredStub(
            Product::class,
            [
                'getId' => Uuid::fromString(self::UUID),
                'getSku' => 123,
                'getTitle' => 'test',
                'getDescription' => 'Cool PC',
                'getPriceInEuroCents' => 1234,
            ]
        );

        $productStubTwo = self::createConfiguredStub(
            Product::class,
            [
                'getId' => null,
                'getSku' => 567,
                'getTitle' => null,
                'getDescription' => 'Heavy phone',
                'getPriceInEuroCents' => 9999,
            ]
        );

        $expectedData = [
            [
                'id' => self::UUID,
                'sku' => 123,
                'title' => 'test',
                'description' => 'Cool PC',
                'price_in_euro_cents' => 1234
            ],
            [
                'id' => null,
                'sku' => 567,
                'title' => null,
                'description' => 'Heavy phone',
                'price_in_euro_cents' => 9999
            ]
        ];

        self::assertSame($expectedData, $this->unit->serializeBatch([$productStubOne, $productStubTwo]));
    }

    /**
     * @dataProvider serializeProductTestDataProvider
     *
     * @param array<string, mixed> $productData
     * @param array<string, mixed> $expectedOutput
     */
    public function testSerializeProduct(array $productData, array $expectedOutput): void
    {
        $productStub = self::createConfiguredStub(Product::class, $productData);

        self::assertSame($expectedOutput, $this->unit->serialize($productStub));
    }

    /**
     * @return Traversable<string, array<string, mixed>>
     */
    public static function serializeProductTestDataProvider(): Traversable
    {
        yield 'All fields are set' => [
            'productData' => [
                'getId' => Uuid::fromString(self::UUID),
                'getSku' => 123,
                'getTitle' => 'A Title',
                'getDescription' => 'super long stuff goes here',
                'getPriceInEuroCents' => 999
            ],
            'expectedOutput' => [
                'id' => self::UUID,
                'sku' => 123,
                'title' => 'A Title',
                'description' => 'super long stuff goes here',
                'price_in_euro_cents' => 999
            ]
        ];

        yield 'Fields are not set' => [
            'productData' => [
                'getId' => null,
                'getSku' => null,
                'getTitle' => null,
                'getDescription' => null,
                'getPriceInEuroCents' => null
            ],
            'expectedOutput' => [
                'id' => null,
                'sku' => null,
                'title' => null,
                'description' => null,
                'price_in_euro_cents' => null
            ]
        ];
    }
}
