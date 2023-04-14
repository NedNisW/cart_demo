<?php

declare(strict_types=1);

namespace App\Product\Service;

use App\Product\Entity\Product;
use App\Product\Exception\ProductNotFoundException;
use App\Product\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

class ProductInfoService
{
    private const QUERY_LIMIT = 100;

    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function getProduct(Uuid $uuid): Product
    {
        $product = $this->productRepository->find($uuid);
        if (!$product) {
            throw new ProductNotFoundException($uuid);
        }

        return $product;
    }

    /**
     * @return array<Product>
     */
    public function getProducts(int $page, int $limit = self::QUERY_LIMIT): array
    {
        return $this->productRepository->findBy(criteria: [], limit: $limit, offset: ($page - 1) * $limit);
    }

    public function getTotalNumOfProducts(): int
    {
        return $this->productRepository->count([]);
    }
}
