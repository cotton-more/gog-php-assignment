<?php namespace GOG\CartBundle;


class CartLimitException extends \RuntimeException
{
    public function __construct(CartManager $cartManager, $code = 0)
    {
        $message = sprintf('Cart size is limited to %d', $cartManager->getCartSize());
        parent::__construct($message, $code);
    }
}