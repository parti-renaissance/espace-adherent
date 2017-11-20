<?php

namespace Tests\AppBundle\Controller\Api;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functionnal
 */
class UserCanUnsubscribeControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testIndex()
    {
        $this->client->request(Request::METHOD_GET, sprintf('/api/can_unsubscribe/%s', LoadAdherentData::ADHERENT_1_UUID));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->client->request(Request::METHOD_GET, sprintf('/api/can_unsubscribe/%s', LoadAdherentData::ADHERENT_5_UUID));
        $this->assertTrue($this->client->getResponse()->isForbidden());

        $this->client->request(Request::METHOD_GET, sprintf('/api/can_unsubscribe/%s', LoadAdherentData::ADHERENT_8_UUID));
        $this->assertTrue($this->client->getResponse()->isForbidden());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([LoadAdherentData::class]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
