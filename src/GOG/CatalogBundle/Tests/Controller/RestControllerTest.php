<?php namespace tests\GOGCatalogBundle\Controller;



use Doctrine\ORM\EntityManager;
use GOG\CatalogBundle\Entity\Product;
use GOG\CatalogBundle\Service\ProductManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestControllerTest extends WebTestCase
{
    /** @var  Client */
    private $client;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ProductManager
     */
    private $manager;

    public function setUp()
    {
        static::bootKernel();

        $this->em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->client = static::createClient();

        $this->manager = static::$kernel->getContainer()->get('gog_catalog.product_manager');
    }

    /** @test */
    public function it_should_patch_product()
    {
        $newProduct = [
            'title' => 'A brand new game',
            'price' => mt_rand(1, 10),
            'currency' => 'USD',
        ];

        /** @var Product $product */
        $product = $this->manager
            ->createProduct()
            ->setPrice($newProduct['price'])
            ->setTitle($newProduct['title'])
            ->setCurrency($newProduct['currency']);
        $this->manager->saveProduct($product);

        $this->client->request(
            'PATCH', '/api/v1/catalog/products/'.$product->getId(), [
                'gog_catalog_product' => [
                    'price' => 20,
                    'title' => 'The brand new game',
                ]
            ]
        );

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        static::assertNotEquals($newProduct['title'], $content['product']['title']);
        static::assertNotEquals($newProduct['price'], $content['product']['price']);
    }

    /** @test */
    public function it_should_get_products()
    {
        $this->client->request('GET', '/api/v1/catalog/products');

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        static::assertCount(3, $data['result']['products']);

        static::assertGreaterThanOrEqual(1, $data['result']['current_page']);

        static::assertGreaterThanOrEqual(2, $data['result']['pages']);
    }
    
    /** @test */
    public function it_should_create_new_product()
    {
        $newProduct = [
            'gog_catalog_product' => [
                'title' => 'A brand new game',
                'price' => mt_rand(1, 10),
                'currency' => array_keys(Product::getCurrencyChoices())[mt_rand(0,3)],
            ],
        ];

        $this->client->request(
            'POST', '/api/v1/catalog/products', $newProduct
        );

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /** @test */
    public function it_should_get_400_on_posting_invalid_data()
    {
        $newProduct = [
            'gog_catalog_product' => [
                'title-fail' => 'A brand new game',
                'price-fail' => mt_rand(1, 10),
                'currency' => 'TST',
            ],
        ];

        $this->client->request(
            'POST', '/api/v1/catalog/products', $newProduct
        );

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /** @test */
    public function it_should_create_and_delete()
    {
        $newProduct = [
            'gog_catalog_product' => [
                'title' => 'A brand new game',
                'price' => mt_rand(1, 10),
                'currency' => array_keys(Product::getCurrencyChoices())[mt_rand(0,3)],
            ],
        ];

        $this->client->request(
            'POST', '/api/v1/catalog/products', $newProduct
        );

        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->client->request(
            'DELETE',
            '/api/v1/catalog/products/'.$responseData['product']['id']
        );

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}