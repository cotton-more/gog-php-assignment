<?php namespace GOG\CartBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use GOG\CartBundle\CartLimitException;
use GOG\CartBundle\CartManager;
use GOG\CartBundle\Entity\Cart;
use GOG\CatalogBundle\Entity\Product;
use GOG\CatalogBundle\Service\ProductManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RestController
 * @package GOG\CartBundle\Controller
 */
class RestController extends FOSRestController
{
    /**
     * @var CartManager
     */
    public $cartManager;

    /**
     * @var ProductManager
     */
    public $productManager;

    public function getCartAction($cartCode)
    {
        if ($cart = $this->cartManager->getCartByCode($cartCode)) {
            $view = View::create([
                'cart' => $cart,
            ], Response::HTTP_OK);

            $view->getContext()->addGroup('cart_api');

            return $this->handleView($view);
        }

        return $this->handleView($this->onCartNotFoundError($cartCode));
    }

    public function postCartAction()
    {
        $manager = $this->cartManager;
        
        $cart = $manager->createCart();
        $manager->saveCart($cart);
        
        return $this->handleView($this->onCreateCartSuccess($cart));
    }

    public function putCartAddProductAction(Request $request, $cartCode)
    {
        $cart = $this->cartManager->getCartByCode($cartCode);
        if (null === $cart) {
            return $this->handleView($this->onCartNotFoundError($cartCode));
        }

        $productId = $request->request->get('product_id');
        if ($product = $this->productManager->findById($productId)) {
            $qty = (int) $request->request->getDigits('qty', 1);

            try {
                $this->cartManager->putProduct($cart, $product, $qty);
            } catch (CartLimitException $ex) {
                return $this->handleView($this->onCartException($ex, Response::HTTP_BAD_REQUEST));
            }

            return $this->handleView($this->onCartAddProductSuccess($cart));
        }

        return $this->handleView($this->onProductNotFoundError($productId));
    }

    public function deleteProductAction($cartCode, $productId)
    {
        $cart = $this->cartManager->getCartByCode($cartCode);
        if (null === $cart) {
            return $this->handleView($this->onCartNotFoundError($cartCode));
        }

        if ($product = $this->productManager->findById($productId)) {
            $this->cartManager->deleteProduct($cart, $product);

            return $this->handleView($this->onCartDeleteProductSuccess($cart));
        }

        return $this->handleView($this->onProductNotFoundError($productId));
    }

    /**
     * @param Cart $cart
     * @return View
     */
    private function onCreateCartSuccess(Cart $cart)
    {
        $view = View::create([
            'cart' => $cart->getCode(),
        ], Response::HTTP_CREATED);
        
        return $view;
    }

    /**
     * @param $cartCode
     * @return View
     */
    private function onCartNotFoundError($cartCode)
    {
        return View::create([
            'error' => sprintf('Cart %s not found', $cartCode),
            'code' => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }


    /**
     * @param $productId
     * @return View
     */
    private function onProductNotFoundError($productId)
    {
        return View::create([
            'error' => sprintf('Product %s not found', $productId),
            'code' => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param $cart
     * @return View
     */
    private function onCartAddProductSuccess(Cart $cart)
    {
        $view = View::create([
            'cart' => $cart,
        ], Response::HTTP_CREATED);

        $view->getContext()->addGroup('cart_api');

        return $view;
    }

    /**
     * @param Cart $cart
     * @return View
     */
    private function onCartDeleteProductSuccess(Cart $cart)
    {
        $view = View::create([
            'cart' => $cart,
        ], Response::HTTP_OK);

        $view->getContext()->addGroup('cart_api');

        return $view;
    }

    /**
     * @param \Exception $ex
     * @param $statusCode
     * @return View
     */
    private function onCartException(\Exception $ex, $statusCode)
    {
        $view = View::create([
            'error' => $ex->getMessage(),
            'code' => $statusCode,
        ], $statusCode);

        return $view;
    }
}
