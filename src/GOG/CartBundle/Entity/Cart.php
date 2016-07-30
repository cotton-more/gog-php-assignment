<?php

namespace GOG\CartBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="GOG\CartBundle\Repository\CartRepository")
 */
class Cart
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
     * @ORM\Column(name="code", type="string", length=36, unique=true)
     */
    private $code;

    /**
     * @var float
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="total", type="integer")
     */
    private $total;

    /**
     * @var ArrayCollection
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\OneToMany(targetEntity="GOG\CartBundle\Entity\CartItem", mappedBy="cart")
     */
    private $items;

    private function __construct($code, $total)
    {
        $this->items = new ArrayCollection();
        $this->setCode($code);
        $this->setTotal(0);
    }

    /**
     * Create a new cart instance
     *
     * @return Cart
     */
    public static function create()
    {
        $code = Uuid::uuid4()->toString();

        return new static($code, 0);
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
     * Set code
     *
     * @param string $code
     *
     * @return Cart
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return Cart
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Get formatted total
     *
     * @return float
     */
    public function getFormattedTotal()
    {
        return $this->getTotal() / 100;
    }

    /**
     * Add item
     *
     * @param CartItem $item
     *
     * @return Cart
     */
    public function addItem(CartItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove item
     *
     * @param CartItem $item
     */
    public function removeItem(CartItem $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * Get items
     *
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    public function recalculate()
    {
        $total = 0;

        $this->getItems()->forAll(function ($key, CartItem $item) use (&$total) {
            $total += $item->getTotal();

            return true;
        });

        $this->setTotal($total);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->getItems()->count();
    }
}
