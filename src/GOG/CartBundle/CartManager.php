<?php namespace GOG\CartBundle;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use GOG\CartBundle\Entity\Cart;
use GOG\CartBundle\Entity\CartItem;
use GOG\CatalogBundle\Entity\Product;

class CartManager
{
    /**
     * @var EntityManager
     */
    private $em;
    private $entityClass;
    private $itemClass;

    private $cartSize;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    public function __construct(EntityManager $em, $entityClass, $itemClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
        $this->itemClass = $itemClass;

        $this->repository = $em->getRepository($entityClass);
    }

    /**
     * @return int
     */
    public function getCartSize()
    {
        return $this->cartSize;
    }

    /**
     * @param int $cartSize
     */
    public function setCartSize($cartSize)
    {
        $this->cartSize = $cartSize;
    }

    /**
     * @return Cart
     */
    public function createCart()
    {
        /** @var Cart $cartClass */
        $cartClass = $this->entityClass;

        return $cartClass::create();
    }

    /**
     * @param Product $product
     * @param int $qty
     * @return CartItem
     */
    public function createCartItem($product, $qty)
    {
        /** @var CartItem $cartItemClass */
        $cartItemClass = $this->itemClass;

        return $cartItemClass::create($product, $qty);
    }

    public function saveCart(Cart $cart)
    {
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * @param $cartCode
     * @return null|Cart
     */
    public function getCartByCode($cartCode)
    {
        $cart = $this->repository->findOneBy([
            'code' => $cartCode,
        ]);

        return $cart;
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @param int $qty
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GOG\CartBundle\CartLimitException
     */
    public function putProduct(Cart $cart, Product $product, $qty = 1)
    {
        if (1 > $qty) {
            return;
        }

        $criteria = Criteria::create()->where(Criteria::expr()->eq('product', $product));

        /** @var CartItem $cartItem */
        if ($cartItem = $cart->getItems()->matching($criteria)->first()) {
            $cartItem->setQuantity($qty);
        } elseif ($cart->getSize() < $this->getCartSize()) {
            $cartItem = $this->createCartItem($product, $qty);
            $cartItem->setCart($cart);

            $cart->addItem($cartItem);
        } else {
            throw new CartLimitException($this);
        }

        $cart->recalculate();

        $this->em->persist($cartItem);
        $this->em->persist($cart);
        $this->em->flush();
    }

    /**
     * @param Cart $cart
     * @param Product $product
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteProduct(Cart $cart, Product $product)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('product', $product));

        /** @var CartItem $cartItem */
        if ($cartItem = $cart->getItems()->matching($criteria)->first()) {
            $cart->removeItem($cartItem);
            $this->em->remove($cartItem);

            $cart->recalculate();
        }

        $this->em->persist($cart);
        $this->em->flush();
    }
}