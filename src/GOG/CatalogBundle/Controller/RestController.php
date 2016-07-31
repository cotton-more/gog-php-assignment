<?php namespace GOG\CatalogBundle\Controller;


use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use GOG\CatalogBundle\Form\ProductFormFactory;
use GOG\CatalogBundle\Service\ProductPager;
use GOG\CatalogBundle\Service\ProductManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RestController extends FOSRestController
{
    /**
     * @var ProductManager
     */
    public $productManager;

    /**
     * @var ProductPager
     */
    public $productPager;

    /**
     * @ApiDoc(
     *     description="List all of the products",
     *     statusCodes={
     *      200="Success"
     *     },
     *     section="catalog"
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function getProductsAction(Request $request)
    {
        $page = $request->query->get('page', 0);
        $pager = $this->productPager->create($page);

        $result = $this->productManager->getAllProductsQuery($pager);

        $view = $this->view([
            'result' => $result,
        ], 200);

        $view->getContext()->addGroup('cart_api');

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *     description="Add a new product",
     *     statusCodes={
     *      201="A product was created",
     *      400="Invalid reqiest",
     *     },
     *     parameters={
     *      {"name"="gog_catalog_product[title]", "required"=false, "dataType"="string", "description"="A product title"},
     *      {"name"="gog_catalog_product[price]", "required"=false, "dataType"="float", "description"="A product price"},
     *      {"name"="gog_catalog_product[currency]", "required"=false, "dataType"="string", "description"="A product currency"},
     *     },
     *     section="catalog"
     * )
     * @param Request $request
     * @return Response
     */
    public function postProductAction(Request $request)
    {
        $product = $this->productManager->createProduct();

        $form = $this->get('gog_catalog.form_factory.product')->createForm();
        $form->setData($product);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->productManager->saveProduct($product);

            $view = $this->onPostProductSuccess($form);

            return $this->handleView($view);
        }

        return $this->handleView(
            View::create([
                'error' => trim($form->getErrors()) ?: 'Invalid request',
                'code' => Response::HTTP_BAD_REQUEST,
            ], 400)
        );
    }

    /**
     * @ApiDoc(
     *     description="Remove a product",
     *     statusCodes={
     *      204="Success"
     *     },
     *     requirements={
     *      {"name"="id", "dataType"="integer", "description"="A product id"}
     *     },
     *     section="catalog"
     * )
     *
     * @param $id
     * @return Response
     */
    public function deleteProductAction($id)
    {
        if ($product = $this->productManager->findById($id)) {
            $this->productManager->deleteProduct($product);
        }

        return $this->handleView(View::create(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * @ApiDoc(
     *     description="Update product title and/or price",
     *     requirements={
     *      {"name"="id", "dataType"="integer", "description"="A product id"}
     *     },
     *     parameters={
     *      {"name"="gog_catalog_product[title]", "required"=false, "dataType"="string", "description"="A product title"},
     *      {"name"="gog_catalog_product[price]", "required"=false, "dataType"="float", "description"="A product price"},
     *     },
     *     section="catalog"
     * )
     *
     * @param Request $request
     * @param integer $id
     * @return Response
     */
    public function patchProductAction(Request $request, $id)
    {
        $product = $this->productManager->findById($id);

        if (null === $product) {
            return $this->handleView($this->onProductNotFound($id));
        }

        $form = $this->get('gog_catalog.form_factory.update_product')->createForm();
        $form->setData($product);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->productManager->saveProduct($product);

            return $this->handleView($this->onPatchProductSuccess($form));
        }

        return $this->handleView($this->onPatchProductError($form));
    }

    /**
     * @param $id
     * @return View
     */
    private function onProductNotFound($id)
    {
        return View::create([
            'error' => sprintf('Product %s not found', $id),
            'code' => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param FormInterface $form
     * @return View
     */
    private function onPatchProductError(FormInterface $form)
    {
        return View::create([
            'code' => Response::HTTP_BAD_REQUEST,
            'error' => trim($form->getErrors()),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param FormInterface $form
     * @return View
     */
    private function onPostProductSuccess(FormInterface $form)
    {
        return View::create([
            'product' => $form->getData(),
        ], Response::HTTP_CREATED);
    }

    /**
     * @param FormInterface $form
     * @return View
     */
    private function onPatchProductSuccess(FormInterface $form)
    {
        return View::create([
            'product' => $form->getData(),
        ], Response::HTTP_OK);
    }
}