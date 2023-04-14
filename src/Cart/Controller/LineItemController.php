<?php
declare(strict_types=1);

namespace App\Cart\Controller;

use App\Cart\Service\CartService;
use App\Cart\Service\LineItemService;
use App\Cart\ValueObject\LineItemUpdateValueObject;
use App\Common\Uuid\UuidService;
use App\Product\Repository\ProductRepository;
use App\Product\Service\ProductInfoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LineItemController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly LineItemService $lineItemService,
        private readonly CartService $cartService,
        private readonly ProductInfoService $productInfoService,
        private readonly UuidService $uuidService,
    ) {
    }

    #[Route('/api/carts/{cartId}/line-items')]
    public function addProductToCart(string $cartId, Request $request): Response
    {
        try {
            $cart = $this->cartService->getCartById(
                $this->uuidService->toUuid($cartId)
            );

            $rawData = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);

            $productId = $rawData['product_id'];
            $product = $this->productInfoService->getProduct($this->uuidService->toUuid($productId));

            $lineItem = $this->lineItemService->createByCartAndProduct($cart, $product);

            return new JsonResponse(
                ['id' => $lineItem->getId()],
                201
            );
        } catch (\Throwable $t) {
            return new JsonResponse(['message' => $t->getMessage()], $t->getCode() ?: 500);
        }
    }

    #[Route(path: '/api/carts/{cartId}/line-items/{lineItemId}', methods: ['DELETE'])]
    public function deleteLineItem(string $cartId, string $lineItemId): Response
    {
        try {
            $this->lineItemService->deleteLineItemByIdAndCart(
                $this->uuidService->toUuid($lineItemId),
                $this->uuidService->toUuid($cartId)
            );

            return new Response(status: 204);
        } catch (\Throwable $t) {
            return new JsonResponse(['message' => $t->getMessage()], $t->getCode() ?: 500);
        }
    }

    #[Route(path: '/api/carts/{cartId}/line-items/{lineItemId}', methods: ['PATCH'])]
    public function updateLineItem(string $cartId, string $lineItemId, Request $request): Response
    {
        try {
            $lineItem = $this->lineItemService->getLineItemForCart(
                $this->uuidService->toUuid($lineItemId),
                $this->uuidService->toUuid($cartId)
            );

            $rawData = json_decode($request->getContent(), true, flags: JSON_THROW_ON_ERROR);
            $lineItemUpdateValueObject = LineItemUpdateValueObject::fromRequestPayload($rawData);

            $this->lineItemService->updateLineItem($lineItem, $lineItemUpdateValueObject);

            return new Response(status: 204);
        } catch (\Throwable $t) {
            return new JsonResponse(['message' => $t->getMessage()], $t->getCode() ?: 500);
        }
    }
}