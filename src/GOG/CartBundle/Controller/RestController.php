<?php namespace GOG\CartBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use GOG\CartBundle\CartLimitException;
use GOG\CartBundle\CartManager;
use GOG\CartBundle\Entity\Cart;
use GOG\CatalogBundle\Entity\Product;
use GOG\CatalogBundle\Service\ProductManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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

    /**
     * @ApiDoc(
     *     description="List all the products in the cart",
     *     requirements={
     *         {"name"="cartCode", "dataType"="string", "description"="Cart code"}
     *     },
     *     statusCodes={
     *         200="Success",
     *         404="Cart not found"
     *     },
     *     section="cart"
     * )
     *
     * @param $cartCode
     * @return Response
     */
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

    /**
     * @ApiDoc(
     *     description="Create a cart",
     *     statusCodes={
     *      201="A cart was created"
     *     },
     *     section="cart"
     * )
     * @return Response
     */
    public function postCartAction()
    {
        $manager = $this->cartManager;
        
        $cart = $manager->createCart();
        $manager->saveCart($cart);
        
        return $this->handleView($this->onCreateCartSuccess($cart));
    }

    /**
     * @ApiDoc(
     *     description="Add a product to the cart",
     *     statusCodes={
     *      201="A product was added to the cart",
     *      400="An invalid request",
     *      404="A product or a cart can not be found"
     *     },
     *     requirements={
     *      {"name"="cartCode", "dataType"="string", "description"="Cart code"}
     *     },
     *     parameters={
     *      {"name"="product_id", "required"=true, "dataType"="integer", "description"="A product id"},
     *      {"name"="qty", "required"=false, "dataType"="integer", "description"="Product quantity. Default is 1"}
     *     },
     *     section="cart"
     * )
     *
     * @param Request $request
     * @param string $cartCode
     * @return Response
     */
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

    /**
     * @ApiDoc(
     *     description="Remove product from the cart",
     *     tatusCodes={
     *      200="A product was deleted",
     *      404="A product or a cart can not be found"
     *     },
     *     requirements={
     *      {"name"="cartCode", "dataType"="string", "description"="Cart code"},
     *      {"name"="productId", "dataType"="integer", "description"="A product id"}
     *     },
     *     section="cart"
     * )
     *
     * @param $cartCode
     * @param $productId
     * @return Response
     */
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
