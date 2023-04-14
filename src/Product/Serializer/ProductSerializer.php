<?php
declare(strict_types=1);

namespace App\Product\Serializer;

use App\Product\Entity\Product;

class ProductSerializer
{
    /**
     * @param iterable<Product> $products
     */
    public function serializeBatch(iterable $products): array
    {
        $data = [];
        foreach ($products as $product) {
            $data[] = $this->serialize($product);
        }

        return $data;
    }

    public function serialize(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'title' => $product->getTitle(),
            'description' => $product->getDescription(),
            'price_in_euro_cents' => $product->getPriceInEuroCents(),
        ];
    }
}