<?php

declare(strict_types=1);

namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Entity\LineItem;
use App\Cart\Exception\LineItemNotFoundException;
use App\Cart\Exception\ProductAlreadyInCartException;
use App\Cart\Repository\LineItemRepository;
use App\Cart\ValueObject\LineItemUpdateValueObject;
use App\Product\Entity\Product;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

class LineItemService
{
    public function __construct(
        private readonly LineItemRepository $lineItemRepository,
    ) {
    }

    public function getLineItemForCart(Uuid $lineItemId, Uuid $cartId): LineItem
    {
        $lineItem = $this->lineItemRepository->findByIdAndCart($lineItemId, $cartId);

        if (!$lineItem) {
            throw new LineItemNotFoundException(
                sprintf('Line Item with ID "%s" not found.', $lineItemId)
            );
        }

        return $lineItem;
    }

    public function getLineItemByCartAndSku(Uuid $cartId, int $sku): LineItem
    {
        $lineItem = $this->lineItemRepository->findByCartAndProduct($cartId, $sku);

        if (!$lineItem) {
            throw new LineItemNotFoundException(
                sprintf('Line Item for Cart "%s" and SKU "%d" not found.', $cartId, $sku)
            );
        }

        return $lineItem;
    }

    public function deleteLineItemByIdAndCart(Uuid $lineItemId, Uuid $cartId): void
    {
        $this->deleteLineItem(
            $this->getLineItemForCart($lineItemId, $cartId)
        );
    }

    public function deleteLineItem(LineItem $lineItem): void
    {
        $this->lineItemRepository->delete($lineItem);
    }

    /**
     * @throws ProductAlreadyInCartException
     */
    public function createByCartAndProduct(Cart $cart, Product $product, int $amount = 1): LineItem
    {
        $cartId = $cart->getId();
        $productId = $product->getId();
        if (!$cartId || !$productId) {
            throw new InvalidArgumentException('Either Cart or Product has no ID set.');
        }

        if ($this->lineItemRepository->existByCartAndProduct($cartId, $productId)) {
            throw new ProductAlreadyInCartException($cartId, $productId);
        }

        $lineItem = LineItem::createForCartAndProduct($cart, $product, $amount);
        $this->lineItemRepository->save($lineItem);

        return $lineItem;
    }

    public function updateLineItem(LineItem $lineItem, LineItemUpdateValueObject $updateValues): void
    {
        $newAmount = $updateValues->getNewAmount();
        if ($newAmount !== null && $newAmount <= 0) {
            $this->deleteLineItem($lineItem);

            return;
        }

        if ($newAmount > 0) {
            $lineItem->setAmount($newAmount);
        }

        $this->lineItemRepository->save($lineItem);
    }
}
