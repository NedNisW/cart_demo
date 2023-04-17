<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cart\Controller;

use App\Cart\Controller\LineItemController;
use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Service\CartService;
use App\Cart\Service\LineItemService;
use App\Cart\ValueObject\LineItemUpdateValueObject;
use App\Common\Uuid\UuidService;
use App\Product\Entity\Product;
use App\Product\Service\ProductInfoService;
use DomainException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class LineItemControllerTest extends TestCase
{
    private readonly LineItemService&MockObject $lineItemServiceMock;
    private readonly CartService&MockObject $cartServiceMock;
    private readonly ProductInfoService&MockObject $productInfoServiceMock;
    private readonly UuidService&MockObject $uuidServiceMock;

    private readonly LineItemController $unit;

    protected function setUp(): void
    {
        $this->lineItemServiceMock = self::createMock(LineItemService::class);
        $this->cartServiceMock = self::createMock(CartService::class);
        $this->productInfoServiceMock = self::createMock(ProductInfoService::class);
        $this->uuidServiceMock = self::createMock(UuidService::class);

        $this->unit = new LineItemController(
            $this->lineItemServiceMock,
            $this->cartServiceMock,
            $this->productInfoServiceMock,
            $this->uuidServiceMock
        );
    }

    public function testItAddsItemToTheCart(): void
    {
        $requestMock = self::createMock(Request::class);
        $requestMock->method('getContent')->willReturn(json_encode(['product_id' => 'PRODUCT_UUID']));

        $lineItemStub = self::createStub(LineItem::class);
        $lineItemStub->method('getId')
            ->willReturn(self::createConfiguredStub(Uuid::class, ['__toString' => 'LINE_ITEM_ID']));

        $cartStub = self::createStub(Cart::class);
        $cartUuidStub = self::createStub(Uuid::class);

        $productStub = self::createStub(Product::class);
        $productUuidStub = self::createStub(Uuid::class);

        $this->uuidServiceMock->method('toUuid')->willReturnMap([
            ['CART_UUID', $cartUuidStub],
            ['PRODUCT_UUID', $productUuidStub]
        ]);

        $this->cartServiceMock->method('getCartById')->with($cartUuidStub)->willReturn($cartStub);
        $this->productInfoServiceMock->method('getProduct')->with($productUuidStub)->willReturn($productStub);

        $this->lineItemServiceMock
            ->expects(self::once())
            ->method('createByCartAndProduct')
            ->with($cartStub, $productStub)
            ->willReturn($lineItemStub);

        $response = $this->unit->addProductToCart('CART_UUID', $requestMock);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame(
            ['id' => 'LINE_ITEM_ID'],
            json_decode((string) $response->getContent(), true)
        );
    }

    #[TestWith(['exceptionCode' => 0])]
    #[TestWith(['exceptionCode' => 429])]
    public function testAddProductToCartResponseOnException(int $exceptionCode): void
    {
        $requestStub = self::createStub(Request::class);
        $uuidStub = self::createStub(Uuid::class);

        $this->uuidServiceMock->method('toUuid')->with('ABC')->willReturn($uuidStub);
        $this->cartServiceMock
            ->method('getCartById')
            ->with($uuidStub)
            ->willThrowException(new DomainException('BROKEN', $exceptionCode));

        $response = $this->unit->addProductToCart('ABC', $requestStub);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($exceptionCode ?: 500, $response->getStatusCode());
        self::assertSame(
            ['message' => 'BROKEN'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testDeleteLineItemSuccessResult(): void
    {
        $cartUuidStub = self::createStub(Uuid::class);
        $lineItemUuidStub = self::createStub(Uuid::class);

        $this->uuidServiceMock->method('toUuid')->willReturnMap([
            ['CART_UUID', $cartUuidStub],
            ['LINE_ITEM_UUID', $lineItemUuidStub],
        ]);

        $this->lineItemServiceMock
            ->expects(self::once())
            ->method('deleteLineItemByIdAndCart')
            ->with($lineItemUuidStub, $cartUuidStub);

        $response = $this->unit->deleteLineItem('CART_UUID', 'LINE_ITEM_UUID');

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    #[TestWith(['exceptionCode' => 0])]
    #[TestWith(['exceptionCode' => 429])]
    public function testDeleteLineItemExceptionResponse(int $exceptionCode): void
    {
        $cartUuidStub = self::createStub(Uuid::class);
        $lineItemUuidStub = self::createStub(Uuid::class);

        $this->uuidServiceMock->method('toUuid')->willReturnMap([
            ['CART_UUID', $cartUuidStub],
            ['LINE_ITEM_UUID', $lineItemUuidStub],
        ]);

        $this->lineItemServiceMock
            ->expects(self::once())
            ->method('deleteLineItemByIdAndCart')
            ->willThrowException(new DomainException('BROKEN CODE', $exceptionCode));

        $response = $this->unit->deleteLineItem('CART_UUID', 'LINE_ITEM_UUID');

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($exceptionCode ?: 500, $response->getStatusCode());
        self::assertSame(
            ['message' => 'BROKEN CODE'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testUpdateLineItemSuccess(): void
    {
        $cartUuidStub = self::createStub(Uuid::class);
        $lineItemUuidStub = self::createStub(Uuid::class);
        $lineItemStub = self::createStub(LineItem::class);

        $this->uuidServiceMock->method('toUuid')->willReturnMap([
            ['CART_UUID', $cartUuidStub],
            ['LINE_ITEM_UUID', $lineItemUuidStub],
        ]);

        $requestStub = self::createStub(Request::class);
        $requestStub->method('getContent')->willReturn(json_encode(['quantity' => 55]));

        $this->lineItemServiceMock
            ->method('getLineItemForCart')
            ->with($lineItemUuidStub, $cartUuidStub)
            ->willReturn($lineItemStub);

        $generatedLineItemUpdateValueObject = null;
        $this->lineItemServiceMock
            ->expects(self::once())->method('updateLineItem')
            ->with( // Bad behavior - testing another object than the unit itself
                $lineItemStub,
                self::callback(function (LineItemUpdateValueObject $object) use (&$generatedLineItemUpdateValueObject) {
                    $generatedLineItemUpdateValueObject = $object;
                    self::assertSame(55, $object->getNewQuantity());

                    return true;
                })
            );

        $this->lineItemServiceMock
            ->expects(self::once())
            ->method('updateLineItem')
            ->with(
                $lineItemStub,
                self::callback(
                    // phpcs:ignore
                    function (LineItemUpdateValueObject $lineItemUpdateConfig) use (&$generatedLineItemUpdateValueObject) {
                        return $lineItemUpdateConfig === $generatedLineItemUpdateValueObject;
                    }
                )
            );

        $response = $this->unit->updateLineItem('CART_UUID', 'LINE_ITEM_UUID', $requestStub);

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    #[TestWith(['exceptionCode' => 0])]
    #[TestWith(['exceptionCode' => 429])]
    public function testUpdateLineItemExceptionResponse(int $exceptionCode): void
    {
        $this->uuidServiceMock->method('toUuid')->willThrowException(new DomainException('BROKEN', $exceptionCode));

        $response = $this->unit->updateLineItem('ABC', 'DEF', self::createStub(Request::class));

        self::assertSame($exceptionCode ?: 500, $response->getStatusCode());
        self::assertSame(
            ['message' => 'BROKEN'],
            json_decode((string) $response->getContent(), true)
        );
    }
}
