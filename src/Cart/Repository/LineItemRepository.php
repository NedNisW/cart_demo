<?php
declare(strict_types=1);

namespace App\Cart\Repository;

use App\Cart\Entity\LineItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class LineItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LineItem::class);
    }

    public function findByCartAndProduct(Uuid $cartId, int $sku): ?LineItem
    {
        return $this->findOneBy(['cart' => $cartId, 'product' => $sku]);
    }

    public function findByIdAndCart(Uuid $lineItemId, Uuid $cartId): ?LineItem
    {
        return $this->findOneBy(['id' => $lineItemId, 'cart' => $cartId]);
    }

    public function existByCartAndProduct(Uuid $cartId, Uuid $productId): bool
    {
        return 0 < $this->count(['cart' => $cartId, 'product' => $productId]);
    }

    public function save(LineItem $lineItem, bool $flush = true): void
    {
        $this->getEntityManager()->persist($lineItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function delete(LineItem $lineItem, bool $flush = true): void
    {
        $this->getEntityManager()->remove($lineItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}