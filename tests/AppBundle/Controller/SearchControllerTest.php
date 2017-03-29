<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class SearchControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, '/recherche');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testSearchCommitteeByName()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/recherche?q=En marche Paris 8&t=committees&offset=0');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('.search__committee__box')->count());
        $this->assertSame('En Marche Paris 8', trim($crawler->filter('h2')->eq(0)->text()));
    }

    public function testSearchCommitteeByPostalCode()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/recherche?q=75008&t=committees&offset=0');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('.search__committee__box')->count());
        $this->assertSame('En Marche Paris 8', trim($crawler->filter('h2')->eq(0)->text()));
    }

    public function testSearchCommitteeByPostalCodes()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/recherche?q=75008,76000&r=150&c=Paris&t=committees&offset=0');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->filter('.search__committee__box')->count());
        $this->assertSame('En Marche Paris 8', trim($crawler->filter('h2')->eq(0)->text()));
        $this->assertSame('En Marche - Comité de Rouen', trim($crawler->filter('h2')->eq(1)->text()));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
