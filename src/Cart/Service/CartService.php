<?php
declare(strict_types=1);

namespace App\Cart\Service;

use App\Cart\Entity\Cart;
use App\Cart\Exception\CartNotFoundException;
use App\Cart\Repository\CartRepository;
use Symfony\Component\Uid\Uuid;

class CartService
{
    public function __construct(
        private readonly CartRepository $cartRepository
    ) {
    }

    public function createCart(): Cart
    {
        $cart = new Cart();
        $this->cartRepository->save($cart);

        return $cart;
    }

    /**
     * @throws CartNotFoundException
     */
    public function getCartById(Uuid $uuid): Cart
    {
        $cart = $this->cartRepository->find($uuid);
        if (!$cart) {
            throw new CartNotFoundException($uuid);
        }

        return $cart;
    }

    /**
     * @throws CartNotFoundException
     */
    public function deleteCartById(Uuid $uuid): void
    {
        $this->deleteCart(
            $this->getCartById($uuid)
        );
    }

    public function deleteCart(Cart $cart): void
    {
        $this->cartRepository->delete($cart);
    }
}