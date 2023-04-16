<?php

declare(strict_types=1);

namespace Tests\Unit\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Exception\LineItemNotFoundException;
use App\Cart\Exception\ProductAlreadyInCartException;
use App\Cart\Repository\LineItemRepository;
use App\Cart\Service\LineItemService;
use App\Cart\ValueObject\LineItemUpdateValueObject;
use App\Product\Entity\Product;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class LineItemServiceTest extends TestCase
{
    private const CART_UUID = '7234875b-9f1c-4f35-adf6-e7a38d268ce7';
    private const LINE_ITEM_UUID = '3d0d0016-3457-49f5-9d5b-8326f56959dd';
    private const PRODUCT_UUID = '326da234-c513-4c65-9827-706d80f4504f';

    private readonly LineItemRepository&MockObject $lineItemRepositoryMock;
    private readonly LineItemService $unit;

    private readonly Uuid $cartUuid;
    private readonly Uuid $lineItemUuid;
    private readonly Uuid $productUuid;

    protected function setUp(): void
    {
        $this->lineItemRepositoryMock = self::createMock(LineItemRepository::class);
        $this->unit = new LineItemService($this->lineItemRepositoryMock);

        $this->cartUuid = Uuid::fromString(self::CART_UUID);
        $this->lineItemUuid = Uuid::fromString(self::LINE_ITEM_UUID);
        $this->productUuid = Uuid::fromString(self::PRODUCT_UUID);
    }

    public function testGetLineItemForCartThrowsExceptionIfNotFound(): void
    {
        self::expectException(LineItemNotFoundException::class);

        $this->lineItemRepositoryMock
            ->method('findByIdAndCart')
            ->with($this->lineItemUuid, $this->cartUuid)
            ->willReturn(null);

        $this->unit->getLineItemForCart($this->lineItemUuid, $this->cartUuid);
    }

    public function testGetLineItemForCartReturnsLineItem(): void
    {
        $lineItemStub = self::createStub(LineItem::class);

        $this->lineItemRepositoryMock
            ->method('findByIdAndCart')
            ->with($this->lineItemUuid, $this->cartUuid)
            ->willReturn($lineItemStub);

        self::assertSame($lineItemStub, $this->unit->getLineItemForCart($this->lineItemUuid, $this->cartUuid));
    }

    public function testDeleteLineItemByIdAndCartThrowsExceptionIfLineItemNotFound(): void
    {
        self::expectException(LineItemNotFoundException::class);

        $this->lineItemRepositoryMock
            ->method('findByIdAndCart')
            ->with($this->lineItemUuid, $this->cartUuid)
            ->willReturn(null);
        $this->lineItemRepositoryMock->expects(self::never())->method('delete');

        $this->unit->deleteLineItemByIdAndCart($this->lineItemUuid, $this->cartUuid);
    }

    public function testDeleteLineItemByIdAndCartDeletesLineItem(): void
    {
        $lineItemStub = self::createStub(LineItem::class);

        $this->lineItemRepositoryMock
            ->method('findByIdAndCart')
            ->with($this->lineItemUuid, $this->cartUuid)
            ->willReturn($lineItemStub);

        $this->lineItemRepositoryMock->expects(self::once())->method('delete')->with($lineItemStub);

        $this->unit->deleteLineItemByIdAndCart($this->lineItemUuid, $this->cartUuid);
    }

    public function testDeleteLineItemCallsTheRepositoryDeleteMethod(): void
    {
        $lineItemStub = self::createStub(LineItem::class);

        $this->lineItemRepositoryMock->expects(self::once())->method('delete')->with($lineItemStub);

        $this->unit->deleteLineItem($lineItemStub);
    }

    public function testCreateByCartAndProductCartHasNoId(): void
    {
        self::expectException(InvalidArgumentException::class);

        $cart = self::createStub(Cart::class);
        $product = self::createStub(Product::class);
        $product->method('getId')->willReturn($this->productUuid);

        $this->unit->createByCartAndProduct($cart, $product);
    }

    public function testCreateByCartAndProductProductHasNoId(): void
    {
        self::expectException(InvalidArgumentException::class);

        $cart = self::createStub(Cart::class);
        $cart->method('getId')->willReturn($this->cartUuid);
        $product = self::createStub(Product::class);

        $this->unit->createByCartAndProduct($cart, $product);
    }

    #[TestWith([-1])]
    #[TestWith([0])]
    public function testCreateByCartAndProductQuantityToLow(int $quantity): void
    {
        self::expectException(InvalidArgumentException::class);

        $cart = self::createStub(Cart::class);
        $cart->method('getId')->willReturn($this->cartUuid);
        $product = self::createStub(Product::class);
        $product->method('getId')->willReturn($this->productUuid);

        $this->unit->createByCartAndProduct($cart, $product, $quantity);
    }

    public function testCreateByCartAndProductLineItemExistsAlready(): void
    {
        self::expectException(ProductAlreadyInCartException::class);

        $cartStub = self::createStub(Cart::class);
        $cartStub->method('getId')->willReturn($this->cartUuid);
        $productStub = self::createStub(Product::class);
        $productStub->method('getId')->willReturn($this->productUuid);

        $this->lineItemRepositoryMock
            ->method('existByCartAndProduct')
            ->with($this->cartUuid, $this->productUuid)
            ->willReturn(true);

        $this->unit->createByCartAndProduct($cartStub, $productStub);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testCreateByCartAndProductCreatesLineItem(bool $overrideDefaultQty): void
    {
        $cartStub = self::createStub(Cart::class);
        $cartStub->method('getId')->willReturn($this->cartUuid);
        $productStub = self::createStub(Product::class);
        $productStub->method('getId')->willReturn($this->productUuid);

        $savedLineItem = null;

        $this->lineItemRepositoryMock
            ->method('existByCartAndProduct')
            ->with($this->cartUuid, $this->productUuid)
            ->willReturn(false);

        $this->lineItemRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(
                function (LineItem $lineItem) use ($cartStub, $productStub, $overrideDefaultQty, &$savedLineItem) {
                    $savedLineItem = $lineItem;

                    self::assertSame($cartStub, $lineItem->getCart());
                    self::assertSame($productStub, $lineItem->getProduct());
                    self::assertSame($overrideDefaultQty ? 99 : 1, $lineItem->getQuantity());
                    return true;
                }
            ));

        $callParams = [$cartStub, $productStub];
        if ($overrideDefaultQty) {
            $callParams[] = 99;
        }
        $gottenLineItem = $this->unit->createByCartAndProduct(...$callParams);

        self::assertSame($savedLineItem, $gottenLineItem);
    }

    #[TestWith([-10])]
    #[TestWith([0])]
    public function testUpdateLineItemDeletesLineItemWhenNewQuantityIsBelowOrEqualZero(int $newQty): void
    {
        $updateData = LineItemUpdateValueObject::create()->withNewQuantity($newQty);
        $lineItemStub = self::createStub(LineItem::class);

        $this->lineItemRepositoryMock->expects(self::once())->method('delete')->with($lineItemStub);
        $this->lineItemRepositoryMock->expects(self::never())->method('save');

        $this->unit->updateLineItem($lineItemStub, $updateData);
    }

    public function testUpdateLineItemNullQtyCausesNoUpdateOfQty(): void
    {
        $updateData = LineItemUpdateValueObject::create()->withNewQuantity(null);
        $lineItemMock = self::createMock(LineItem::class);

        $lineItemMock->expects(self::never())->method('setQuantity');
        $this->lineItemRepositoryMock->expects(self::never())->method('delete');
        $this->lineItemRepositoryMock->expects(self::once())->method('save')->with($lineItemMock);

        $this->unit->updateLineItem($lineItemMock, $updateData);
    }

    public function testUpdateLineItemAppliesNewQuantity(): void
    {
        $updateData = LineItemUpdateValueObject::create()->withNewQuantity(12);
        $lineItemMock = self::createMock(LineItem::class);

        $lineItemMock->expects(self::once())->method('setQuantity')->with(12);

        $this->lineItemRepositoryMock->expects(self::never())->method('delete');
        $this->lineItemRepositoryMock->expects(self::once())->method('save')->with($lineItemMock);

        $this->unit->updateLineItem($lineItemMock, $updateData);
    }
}
