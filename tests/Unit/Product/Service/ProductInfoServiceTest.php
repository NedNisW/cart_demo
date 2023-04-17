<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Service;

use App\Product\Entity\Product;
use App\Product\Exception\ProductNotFoundException;
use App\Product\Repository\ProductRepository;
use App\Product\Service\ProductInfoService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Traversable;

class ProductInfoServiceTest extends TestCase
{
    private ProductInfoService $unit;

    private ProductRepository&MockObject $productRepositoryMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = self::createMock(ProductRepository::class);
        $this->unit = new ProductInfoService($this->productRepositoryMock);
    }

    public function testGetProductThrowsExceptionWhenNotFound(): void
    {
        self::expectException(ProductNotFoundException::class);
        self::expectExceptionCode(404);

        $uuid = self::createStub(Uuid::class);

        $this->productRepositoryMock->method('find')->with($uuid)->willReturn(null);

        $this->unit->getProduct($uuid);
    }

    public function testGetProductReturnsLooksForCorrectProduct(): void
    {
        $uuid = self::createStub(Uuid::class);
        $expectedProduct = self::createStub(Product::class);

        $this->productRepositoryMock->method('find')->with($uuid)->willReturn($expectedProduct);

        self::assertSame($expectedProduct, $this->unit->getProduct($uuid));
    }

    /**
     * @dataProvider getProductsSearchQueryIsCorrectTestDataProvider
     */
    public function testGetProductsSearchQueryIsCorrect(int $limit, int $page, int $expectedOffset): void
    {
        $productStubs = [
            self::createStub(Product::class),
            self::createStub(Product::class),
            self::createStub(Product::class),
        ];

        $this->productRepositoryMock
            ->method('findBy')
            ->with([], null, $limit, $expectedOffset)
            ->willReturn($productStubs);

        self::assertSame($productStubs, $this->unit->getProducts($page, $limit));
    }

    public function testGetTotalNumberOfProductsCountsWithoutCriteria(): void
    {
        $this->productRepositoryMock->method('count')->with([])->willReturn(1234);

        $this->assertSame(1234, $this->unit->getTotalNumOfProducts());
    }

    /**
     * @return Traversable<string, array<string, mixed>>
     */
    public static function getProductsSearchQueryIsCorrectTestDataProvider(): Traversable
    {
        yield "Page one" => [
            'limit' => 500,
            'page' => 1,
            'expectedOffset' => 0,
        ];

        yield "Page two" => [
            'limit' => 500,
            'page' => 2,
            'expectedOffset' => 500,
        ];

        yield "Page 8" => [
            'limit' => 500,
            'page' => 8,
            'expectedOffset' => 3500,
        ];
    }
}
