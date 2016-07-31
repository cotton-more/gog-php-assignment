<?php

namespace GOG\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="GOG\CatalogBundle\Repository\ProductRepository")
 */
class Product
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var float
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="price", type="decimal", precision=9, scale=2)
     *
     * @Assert\GreaterThanOrEqual(0)
     */
    private $price;

    /**
     * @var string
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="currency", type="string", length=3)
     *
     * @Assert\Choice(
     *     callback="getCurrencyChoices",
     *     strict=true
     * )
     */
    private $currency;

    public static function getCurrencyChoices()
    {
        return [
            'USD' => 'USD',
            'EUR' => 'EUR',
            'RUB' => 'RUB',
            'PLN' => 'PLN',
        ];
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return  $this->price;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Product
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }
}

