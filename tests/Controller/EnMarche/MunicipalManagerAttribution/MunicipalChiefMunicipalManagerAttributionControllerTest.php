<?php

namespace Tests\App\Controller\EnMarche\MunicipalManagerAttribution;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class MunicipalChiefMunicipalManagerAttributionControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testReferentCanReachMunicipalManagerAttributionForm()
    {
        $this->client->followRedirects();
        $this->authenticateAsAdherent($this->client, 'municipal-chief@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Espace Municipales 2020')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Assesseurs')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Communes')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $cities = $crawler->filter('.datagrid__table-manager tbody tr');
        $this->assertCount(1, $cities);

        $this->assertContains('Lille (59350)', $cities->eq(0)->text());
    }

    public function testAdherentCanNotSeeMunicipalManagerAttributionForm()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $this->client->request(Request::METHOD_GET, '/espace-municipales-2020/responsables-communaux');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
