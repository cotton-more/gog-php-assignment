<?php namespace GOG\CatalogBundle\Service;


use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\Annotation as Serializer;

class ProductPager
{
    /**
     * @var int
     */
    private $limit = 3;

    private $page = 0;

    /**
     * @var Paginator
     */
    private $paginator;

    public function create($page)
    {
        $this->page = abs(filter_var($page, FILTER_SANITIZE_NUMBER_INT)) ?: 1;

        return $this;
    }

    public function paginate($query)
    {
        $limit = $this->limit;
        $page = $this->page;

        $paginator = new Paginator($query);

        $firstResult = 0;
        if (1 < $page) {
            $firstResult = $limit * ($page - 1);
        }

        $paginator->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($limit)
        ;

        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("products")
     * @Serializer\Groups({"cart_api"})
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return $this->paginator->getIterator()->getArrayCopy();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"cart_api"})
     *
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->page;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"cart_api"})
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->paginator->count();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({"cart_api"})
     *
     * @return int
     */
    public function getPages()
    {
        return ceil($this->paginator->count() / $this->limit);
    }
}