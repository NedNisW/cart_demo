<?php

declare(strict_types=1);

namespace App\Product\Controller;

use App\Product\Serializer\ProductSerializer;
use App\Product\Service\ProductInfoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private const DEFAULT_LIMIT = 50;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly ProductInfoService $productInfoService,
        private readonly ProductSerializer $productSerializer
    ) {
    }

    #[Route(path: '/api/products', methods: ['GET'])]
    public function listProducts(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);
        $limit = min((int) $request->query->get('limit', self::DEFAULT_LIMIT), self::MAX_LIMIT);

        $productsCount = $this->productInfoService->getTotalNumOfProducts();
        $products = $this->productInfoService->getProducts($page, $limit);

        return new JsonResponse(
            [
                'page' => $page,
                'per_page' => $limit,
                'total' => $productsCount,
                'products' => $this->productSerializer->serializeBatch($products)
            ],
            200
        );
    }
}
