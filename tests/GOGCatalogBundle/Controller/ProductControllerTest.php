<?php
/**
 * Created by PhpStorm.
 * User: inikulin
 * Date: 26/07/16
 * Time: 19:45
 */

namespace tests\GOGCatalogBundle\Controller;



use GOG\CatalogBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    /** @var  Client */
    private $client;

    /**
     *
     */
    public function setUp()
    {
        $this->client = static::createClient();
    }

    /** @test */
    public function it_should_get_products()
    {
        $this->client->request('GET', '/api/v1/catalog/products');

        $response = $this->client->getResponse();

        static::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        static::assertCount(3, $data['result']['paginator']);

        static::assertGreaterThan(2, $data['result']['pageNumber']);

        static::assertGreaterThan(4, $data['result']['total']);
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
}