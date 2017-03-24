<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadSocialShareData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class SocialShareControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testList()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testShow()
    {
        $this->client->request(Request::METHOD_GET, '/jepartage/culture');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadSocialShareData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
