<?php namespace GOG\CatalogBundle\Service;


use Doctrine\ORM\EntityManager;
use GOG\CatalogBundle\Entity\Product;

class ProductManager
{
    /**
     * @var EntityManager
     */
    private $em;
    private $class;

    /**
     * ProductManager constructor.
     * @param EntityManager $em
     * @param string $class Entity class name
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->class = $class;

        $this->repository = $em->getRepository($class);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getAllProductsQuery(ProductPager $pager = null)
    {
        $query = $this->repository->createQueryBuilder('p')->getQuery();

        if ($pager) {
            $pager->paginate($query);

            return $pager;
        }

        return $query->getResult();
    }

    public function createProduct()
    {
        $class = $this->getClass();

        return new $class;
    }

    public function saveProduct($product)
    {
        $this->em->persist($product);
        $this->em->flush();
    }

    /**
     * @param $id
     * @return null|Product
     */
    public function findById($id)
    {
        return $this->repository->find($id);
    }

    public function deleteProduct(Product $product)
    {
        $this->em->remove($product);
        $this->em->flush();
    }


}