<?php

declare(strict_types=1);

namespace App\Product\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\Column(unique: true)]
    private ?int $sku;

    #[ORM\Column(length: 100)]
    private ?string $title;

    #[ORM\Column(length: 255)]
    private ?string $description;

    #[ORM\Column]
    private ?int $priceInEuroCents;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setSku(?int $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getSku(): ?int
    {
        return $this->sku;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setPriceInEuroCents(?int $priceInEuroCents): self
    {
        $this->priceInEuroCents = $priceInEuroCents;

        return $this;
    }

    public function getPriceInEuroCents(): ?int
    {
        return $this->priceInEuroCents;
    }
}
