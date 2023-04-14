<?php

namespace App\DataFixtures;

use App\Product\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $macbook = (new Product())
            ->setSku(111)
            ->setTitle('MacBook Air, late 2022')
            ->setDescription('Boah, cool')
            ->setPriceInEuroCents(68700);

        $iphone = (new Product())
            ->setSku(222)
            ->setTitle('IPhone 14Pro')
            ->setDescription('Beep Boop')
            ->setPriceInEuroCents(87000);

        $dellXps = (new Product())
            ->setSku(333)
            ->setTitle('Dell XPS 13", Linux Edition')
            ->setDescription('From Dell but still cool')
            ->setPriceInEuroCents('99900');

        $manager->persist($macbook);
        $manager->persist($iphone);
        $manager->persist($dellXps);

        $manager->flush();
    }
}
