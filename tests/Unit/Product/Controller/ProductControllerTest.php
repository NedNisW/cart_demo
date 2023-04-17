<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Controller;

use App\Product\Controller\ProductController;
use App\Product\Entity\Product;
use App\Product\Serializer\ProductSerializer;
use App\Product\Service\ProductInfoService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

class ProductControllerTest extends TestCase
{
    private readonly ProductInfoService&MockObject $productInfoServiceMock;
    private readonly ProductSerializer&MockObject $productSerializerMock;
    private readonly ProductController $unit;

    protected function setUp(): void
    {
        $this->productInfoServiceMock = self::createMock(ProductInfoService::class);
        $this->productSerializerMock = self::createMock(ProductSerializer::class);

        $this->unit = new ProductController($this->productInfoServiceMock, $this->productSerializerMock);
    }

    /**
     * @dataProvider paginationQueryTestDataProvider
     *
     * @param array<string, int> $queryData
     */
    public function testPaginationQuery(array $queryData, int $expectedPage, int $expectedPerPage): void
    {
        $productStubs = $this->getDummyProductResultSet();

        $queryInputBag = new InputBag($queryData);

        $requestStub = self::createStub(Request::class);
        $requestStub->query = $queryInputBag;

        $this->productInfoServiceMock->method('getTotalNumOfProducts')->willReturn(2);
        $this->productInfoServiceMock
            ->expects(self::once())
            ->method('getProducts')
            ->with($expectedPage, $expectedPerPage)
            ->willReturn($productStubs);

        $this->productSerializerMock->expects(self::once())->method('serializeBatch')->with($productStubs);

        $this->unit->listProducts($requestStub);
    }

    public function testListProductReturnsExepctedResponse(): void
    {
        $queryInputBag = new InputBag(['page' => 55, 'per_page' => 78]);
        $requestStub = self::createStub(Request::class);
        $requestStub->query = $queryInputBag;

        $productStubs = $this->getDummyProductResultSet();

        $this->productInfoServiceMock->method('getTotalNumOfProducts')->willReturn(123);
        $this->productInfoServiceMock->method('getProducts')->with(55, 78)->willReturn($productStubs);

        $this->productSerializerMock->method('serializeBatch')->with($productStubs)->willReturn(['a', 'b', 'c']);

        $response = $this->unit->listProducts($requestStub);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode(['page' => 55, 'per_page' => 78, 'total' => 123, 'products' => ['a', 'b', 'c']]),
            $response->getContent()
        );
    }

    /**
     * @return Traversable<array<string, mixed>>
     */
    public static function paginationQueryTestDataProvider(): Traversable
    {
        yield "No Query Data Provided, fall back to default" => [
            'queryData' => [],
            'expectedPage' => 1,
            'expectedPerPage' => 50,
        ];

        yield "Page 5, limit is above max allowed, max allowed should be used" => [
            'queryData' => ['page' => 5, 'per_page' => 999],
            'expectedPage' => 5,
            'expectedPerPage' => 100,
        ];

        yield "Page 5, valid per Page setting" => [
            'queryData' => ['page' => 5, 'per_page' => 99],
            'expectedPage' => 5,
            'expectedPerPage' => 99,
        ];
    }

    /**
     * @return array<int, Product&Stub>
     */
    private function getDummyProductResultSet(): array
    {
        return [
            self::createStub(Product::class),
            self::createStub(Product::class),
        ];
    }
}
