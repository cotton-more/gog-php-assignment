<?php namespace GOG\CatalogBundle\Controller\API;


use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use GOG\CatalogBundle\Form\ProductFormFactory;
use GOG\CatalogBundle\Service\ProductPager;
use GOG\CatalogBundle\Service\ProductManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends FOSRestController
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
     * @var ProductFormFactory
     */
    public $productFormFactory;

    public function getProductsAction(Request $request)
    {
        $page = $request->query->get('page', 0);
        $pager = $this->productPager->create($page);

        $result = $this->productManager->getAllProductsQuery($pager);

        $view = $this->view([
            'result' => $result,
        ], 200);

        return $this->handleView($view);
    }

    public function postProductAction(Request $request)
    {
        $product = $this->productManager->createProduct();

        $form = $this->productFormFactory->createForm();
        $form->setData($product);


        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->productManager->saveProduct($product);

            $view = $this->view($product, 201);

            return $this->handleView($view);
        }

        return $this->handleView(
            View::create([
                'error' => true,
                'code' => 400,
                'message' => (string) $form->getErrors(true) ?: 'Invalid request',
            ], 400)
        );
    }

    public function deleteProductAction($id)
    {
        if ($product = $this->productManager->findById($id)) {
            $this->productManager->deleteProduct($product);
        }

        return $this->handleView(View::create(null, Response::HTTP_NO_CONTENT));
    }
}