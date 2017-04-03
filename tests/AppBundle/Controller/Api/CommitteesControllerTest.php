<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functionnal
 */
class CommitteesControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideApiUrl
     */
    public function testGetCommitteesAction(string $url)
    {
        $this->client->request(Request::METHOD_GET, $url);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertJson($this->client->getResponse()->getContent());
    }

    public function provideApiUrl()
    {
        return [
            ['/api/committees'],
            ['/api/events'],
        ];
    }

    public function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventData::class,
        ]);
    }

    public function tearDown()
    {
        $this->kill([]);

        parent::tearDown();
    }
}
