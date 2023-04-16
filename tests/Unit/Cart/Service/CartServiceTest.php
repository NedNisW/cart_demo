<?php

declare(strict_types=1);

namespace Tests\Unit\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Exception\CartNotFoundException;
use App\Cart\Repository\CartRepository;
use App\Cart\Service\CartService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CartServiceTest extends TestCase
{
    private const CART_UUID = '7234875b-9f1c-4f35-adf6-e7a38d268ce7';

    private readonly CartRepository&MockObject $cartRepositoryMock;
    private readonly CartService $unit;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = self::createMock(CartRepository::class);
        $this->unit = new CartService($this->cartRepositoryMock);
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

    public function testDeleteCartDeletesIt(): void
    {
        $cartStub = self::createStub(Cart::class);

        $this->cartRepositoryMock->expects(self::once())->method('delete')->with($cartStub);

        $this->unit->deleteCart($cartStub);
    }
}
