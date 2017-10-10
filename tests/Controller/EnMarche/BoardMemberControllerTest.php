<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class BoardMemberControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testIndexBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch', 'secret!12345');

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testSearchBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch', 'secret!12345');

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testSavedProfilBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'michelle.dufour@example.ch', 'secret!12345');

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
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
