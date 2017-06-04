<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadLegislativesData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ApiControllerTestTrait;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class LegislativesControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testApiApprovedCommittees()
    {
        $this->client->request(Request::METHOD_GET, '/api/candidates');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);

        // Check the payload
        $this->assertNotEmpty(\GuzzleHttp\json_decode($content, true));
        $this->assertEachJsonItemContainsKey('id', $content);
        $this->assertEachJsonItemContainsKey('name', $content);
        $this->assertEachJsonItemContainsKey('district', $content);
        $this->assertEachJsonItemContainsKey('picture', $content);
        $this->assertEachJsonItemContainsKey('url', $content);
        $this->assertEachJsonItemContainsKey('geojson', $content);
    }

    public function setUp()
    {
        parent::setUp();

        $this->init([
            LoadLegislativesData::class,
        ]);
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
