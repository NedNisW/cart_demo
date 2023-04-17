<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cart\Controller;

use App\Cart\Controller\CartController;
use App\Cart\Entity\Cart;
use App\Cart\Exception\CartNotFoundException;
use App\Cart\Serializer\CartSerializer;
use App\Cart\Serializer\CartSerializerConfig;
use App\Cart\Service\CartService;
use App\Common\Uuid\UuidService;
use DomainException;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class CartControllerTest extends TestCase
{
    private readonly CartService&MockObject $cartServiceMock;
    private readonly CartSerializer&MockObject $cartSerializerMock;
    private readonly UuidService&MockObject $uuidServiceMock;

    private readonly CartController $unit;

    protected function setUp(): void
    {
        $this->cartServiceMock = self::createMock(CartService::class);
        $this->cartSerializerMock = self::createMock(CartSerializer::class);
        $this->uuidServiceMock = self::createMock(UuidService::class);

        $this->unit = new CartController($this->cartServiceMock, $this->cartSerializerMock, $this->uuidServiceMock);
    }

    public function testCreateCartSuccess(): void
    {
        $cartStub = self::createConfiguredStub(
            Cart::class,
            [
                'getId' => self::createConfiguredStub(Uuid::class, ['__toString' => 'CART_ID']),
            ]
        );

        $this->cartServiceMock->method('createCart')->willReturn($cartStub);

        $response = $this->unit->createCart();

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame(
            ['id' => 'CART_ID'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testDeleteCartSuccess(): void
    {
        $uuidStub = self::createStub(Uuid::class);

        $this->uuidServiceMock->method('toUuid')->with('CART_ID')->willReturn($uuidStub);
        $this->cartServiceMock->expects(self::once())->method('deleteCartById')->with($uuidStub);

        $response = $this->unit->deleteCart('CART_ID');

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    /**
     * @param class-string<Exception> $exceptionClass
     * @param int $expectedCode
     */
    #[TestWith(['exceptionClass' => InvalidArgumentException::class, 'expectedCode' => 400])]
    #[TestWith(['exceptionClass' => DomainException::class, 'expectedCode' => 500])]
    public function testDeleteCartExceptionHandling(string $exceptionClass, int $expectedCode): void
    {
        $uuidStub = self::createStub(Uuid::class);
        $this->uuidServiceMock->method('toUuid')->with('CART_ID')->willReturn($uuidStub);

        $this->cartServiceMock
            ->method('deleteCartById')
            ->with($uuidStub)
            ->willThrowException(new $exceptionClass('BROKEN'));

        $response = $this->unit->deleteCart('CART_ID');

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($expectedCode, $response->getStatusCode());
        self::assertSame(
            ['message' => 'BROKEN'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testGetCartSuccess(): void
    {
        $cartUuidStub = self::createStub(Uuid::class);
        $cartStub = self::createStub(Cart::class);

        $this->uuidServiceMock->method('toUuid')->with('CART_ID')->willReturn($cartUuidStub);

        $secondCartSerializerMock = self::createMock(CartSerializer::class);

        $this->cartServiceMock->method('getCartById')->with($cartUuidStub)->willReturn($cartStub);

        $this->cartSerializerMock
            ->expects(self::once())
            ->method('withConfig')
            ->with(self::equalTo(CartSerializerConfig::create(true)))
            ->willReturn($secondCartSerializerMock);

        $secondCartSerializerMock
            ->expects(self::once())
            ->method('serialize')
            ->with($cartStub)
            ->willReturn(['SERIALIZED_CART_DATA']);

        $response = $this->unit->getCart('CART_ID');

        self::assertSame(200, $response->getStatusCode());
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(
            ['SERIALIZED_CART_DATA'],
            json_decode((string) $response->getContent(), true)
        );
    }

    /**
     * @param class-string<Exception> $exceptionClass
     * @param int $expectedCode
     */
    #[TestWith([
        'exceptionClass' => InvalidArgumentException::class,
        'expectedCode' => 400,
    ])]
    #[TestWith([
        'exceptionClass' => DomainException::class,
        'expectedCode' => 500
    ])]
    public function testGetCartExceptionResponse(string $exceptionClass, int $expectedCode): void
    {
        $this->uuidServiceMock->method('toUuid')->willThrowException(new $exceptionClass('BROKEN'));

        $response = $this->unit->getCart('CART_ID');

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame($expectedCode, $response->getStatusCode());
        self::assertSame(
            ['message' => 'BROKEN'],
            json_decode((string) $response->getContent(), true)
        );
    }

    public function testGetCartCartNotFoundResponse(): void
    {
        $cartNotFound = new CartNotFoundException(self::createStub(Uuid::class));
        $this->uuidServiceMock->method('toUuid')->willThrowException($cartNotFound);

        $response = $this->unit->getCart('CART_ID');

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(
            ['message' => 'Cart not found'],
            json_decode((string) $response->getContent(), true)
        );
    }
}
