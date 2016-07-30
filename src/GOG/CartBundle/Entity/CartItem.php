<?php

namespace GOG\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use GOG\CatalogBundle\Entity\Product;
use JMS\Serializer\Annotation as Serializer;

/**
 * CartItem
 *
 * @ORM\Table(name="cart_item")
 * @ORM\Entity(repositoryClass="GOG\CartBundle\Repository\CartItemRepository")
 */
class CartItem
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
     * @var int
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @var int
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var string
     *
     * @Serializer\Groups({"cart_api"})
     *
     * @ORM\Column(name="total", type="integer")
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity="GOG\CartBundle\Entity\Cart", inversedBy="items")
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    private $cart;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="GOG\CatalogBundle\Entity\Product")
     */
    private $product;

    /**
     * @param Product $product
     * @param int $qty
     * @return CartItem
     */
    public static function create(Product $product, $qty = 1)
    {
        $cartItem = new static;

        $cartItem->setProduct($product);
        $cartItem->setPrice($product->getPrice());
        $cartItem->setQuantity($qty);

        return $cartItem;
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
     * Set price
     *
     * @param integer $price
     *
     * @return CartItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return CartItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        $this->recalculate();

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return CartItem
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set cart
     *
     * @param Cart $cart
     *
     * @return CartItem
     */
    public function setCart(Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return CartItem
     */
    public function setProduct(Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"cart_api"})
     *
     * @return string
     */
    public function getProductTitle()
    {
        return $this->getProduct()->getTitle();
    }

    public function recalculate()
    {
        $total = $this->getQuantity() * $this->getPrice();
        
        $this->setTotal($total);
    }
}
