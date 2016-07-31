<?php

namespace GOG\CartBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use GOG\CartBundle\Entity\Cart;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestControllerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    public function setUp()
    {
        static::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');
    }

    /** @test */
    public function it_should_create_cart()
    {
        $client = static::createClient();

        $client->request('POST', '/api/v1/cart/');
        
        $response = $client->getResponse();
        
        static::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);

        $carts = $this->em->getRepository(Cart::class)->findBy([
            'code' => $content['cart'],
        ]);

        static::assertCount(1, $carts);
    }
}
