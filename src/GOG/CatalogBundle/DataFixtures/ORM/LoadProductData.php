<?php namespace GOG\CatalogBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GOG\CatalogBundle\Entity\Product;

class LoadProductData implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = [
            [
                'title' => 'Fallout',
                'price' => 1.99,
                'currency' => 'USD',
            ],
            [
                'title' => "Don’t Starve",
                'price' => 2.99,
                'currency' => 'USD',
            ],
            [
                'title' => "Baldur’s Gate",
                'price' => 3.99,
                'currency' => 'USD',
            ],
            [
                'title' => 'Icewind Dale',
                'price' => 4.99,
                'currency' => 'USD',
            ],
            [
                'title' => 'Icewind Dale',
                'price' => 5.99,
                'currency' => 'USD',
            ],
        ];

        foreach ($data as $item) {
            $product = new Product();

            $product
                ->setTitle($item['title'])
                ->setPrice($item['price'])
                ->setCurrency($item['currency'])
            ;

            $manager->persist($product);
        }

        $manager->flush();
    }
}