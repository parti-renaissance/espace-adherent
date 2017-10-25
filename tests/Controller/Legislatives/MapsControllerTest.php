<?php

namespace Tests\AppBundle\Controller\Legislatives;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\DataFixtures\ORM\LoadLegislativesData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class MapsControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testCandidates()
    {
        $this->client->request(Request::METHOD_GET, 'https://legislatives-en-marche.dev/la-carte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testEvents()
    {
        $this->client->request(Request::METHOD_GET, 'https://legislatives-en-marche.dev/les-evenements');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadLegislativesData::class,
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
