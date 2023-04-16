<?php

declare(strict_types=1);

namespace App\Cart\Controller;

use App\Cart\Exception\CartNotFoundException;
use App\Cart\Serializer\CartSerializer;
use App\Cart\Serializer\CartSerializerConfig;
use App\Cart\Service\CartService;
use App\Common\Uuid\UuidService;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CartSerializer $cartSerializer,
        private readonly UuidService $uuidService,
    ) {
    }

    #[Route('/api/carts', methods: ['POST'])]
    public function createCart(): Response
    {
        return new JsonResponse(
            ['id' => (string) $this->cartService->createCart()->getId()],
            201
        );
    }

    #[Route('/api/carts/{cartId}', methods: ['DELETE'])]
    public function deleteCart(string $cartId): Response
    {
        try {
            $this->cartService->deleteCartById($this->uuidService->toUuid($cartId));
        } catch (Throwable $t) {
            return new JsonResponse(
                ['message' => $t->getMessage()],
                $t instanceof InvalidArgumentException ? 400 : 500
            );
        }

        return new Response(status: 204);
    }

    #[Route('/api/carts/{cartId}', methods: ['GET'])]
    public function getCart(string $cartId): JsonResponse
    {
        try {
            $serializedCart = $this->cartSerializer
                ->withConfig(CartSerializerConfig::create(true))
                ->serialize($this->cartService->getCartById($this->uuidService->toUuid($cartId)));
        } catch (CartNotFoundException $exception) {
            return new JsonResponse(['message' => 'Cart not found'], 404);
        } catch (Throwable $t) {
            return new JsonResponse(
                ['message' => $t->getMessage()],
                $t instanceof InvalidArgumentException ? 400 : 500
            );
        }

        return new JsonResponse($serializedCart);
    }
}
