<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Exception\CartNotFoundException;
use App\Cart\Repository\CartRepository;
use App\Cart\Service\CartService;
use App\Cart\Service\LineItemService;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CartServiceTest extends TestCase
{
    private const CART_UUID = '7234875b-9f1c-4f35-adf6-e7a38d268ce7';

    private readonly CartRepository&MockObject $cartRepositoryMock;
    private readonly LineItemService&MockObject $lineItemServiceMock;

    private readonly CartService $unit;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = self::createMock(CartRepository::class);
        $this->lineItemServiceMock = self::createMock(LineItemService::class);

        $this->unit = new CartService($this->cartRepositoryMock, $this->lineItemServiceMock);
    }

    public function testItCreatesNewCart(): void
    {
        $savedCart = null;
        $this->cartRepositoryMock
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function (Cart $cart) use (&$savedCart) {
                $savedCart = $cart;

                self::assertEmpty($cart->getLineItems());
                self::assertNull($cart->getId());
                self::assertNull($cart->getCreatedAt());
                self::assertNull($cart->getUpdatedAt());

                return true;
            }));

        $gottenCart = $this->unit->createCart();
        self::assertSame($savedCart, $gottenCart);
    }

    public function testGetCartByIdThrowsNotFoundException(): void
    {
        self::expectException(CartNotFoundException::class);

        $uuid = Uuid::fromString(self::CART_UUID);
        $this->cartRepositoryMock->expects(self::once())->method('find')->with($uuid)->willReturn(null);

        $this->unit->getCartById($uuid);
    }

    public function testGetCartByIdFetchesCartAndReturnsIt(): void
    {
        $uuid = Uuid::fromString(self::CART_UUID);
        $cartStub = self::createStub(Cart::class);

        $this->cartRepositoryMock->expects(self::once())->method('find')->with($uuid)->willReturn($cartStub);

        self::assertSame($cartStub, $this->unit->getCartById($uuid));
    }

    public function testDeleteCartByIdThrowsExceptionWhenCartDoesNotExist(): void
    {
        self::expectException(CartNotFoundException::class);

        $uuid = Uuid::fromString(self::CART_UUID);
        $this->cartRepositoryMock->expects(self::once())->method('find')->with($uuid)->willReturn(null);

        $this->unit->deleteCartById($uuid);
    }

    public function testDeleteCartByIdDeletesCart(): void
    {
        $uuid = Uuid::fromString(self::CART_UUID);
        $cartStub = self::createStub(Cart::class);

        $this->cartRepositoryMock->method('find')->with($uuid)->willReturn($cartStub);
        $this->cartRepositoryMock->expects(self::once())->method('delete')->with($cartStub);

        $this->unit->deleteCartById($uuid);
    }

    public function testDeleteCartDeletesItAndAllLineItems(): void
    {
        $lineItemOne = self::createStub(LineItem::class);
        $lineItemTwo = self::createStub(LineItem::class);
        $lineItemsCollection = new ArrayCollection([$lineItemOne, $lineItemTwo]);

        $cartStub = self::createStub(Cart::class);
        $cartStub->method('getLineItems')->willReturn($lineItemsCollection);

        // withConsecutiveCalls was removed with PHPUnit 10 - this is a workaround to
        // still check if all expected calls were made
        $notDeletedLineItems = [$lineItemOne, $lineItemTwo];
        $this->lineItemServiceMock
            ->expects(self::exactly(2))
            ->method('deleteLineItem')
            ->with(
                self::callback(function (LineItem $itemToDelete) use (&$notDeletedLineItems) {
                    $itemIdx = array_search($itemToDelete, $notDeletedLineItems);
                    if (false !== $itemIdx) {
                        unset($notDeletedLineItems[$itemIdx]);
                    }

                    return true;
                }),
                false
            );

        $this->cartRepositoryMock->expects(self::once())->method('delete')->with($cartStub);

        $this->unit->deleteCart($cartStub);

        self::assertEmpty($notDeletedLineItems);
    }
}
