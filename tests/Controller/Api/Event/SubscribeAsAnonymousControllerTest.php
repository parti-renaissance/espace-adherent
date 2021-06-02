<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadCoalitionEventData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class SubscribeAsAnonymousControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testAnonymousCanSubscribeOnEvent()
    {
        $this->client->request(Request::METHOD_POST, sprintf('/api/events/%s/subscribe', LoadCoalitionEventData::EVENT_1_UUID), [], [], [], json_encode([
            'first_name' => 'Joe',
        ]));

        $this->assertStatusCode(400, $this->client);

        $this->client->request(Request::METHOD_POST, sprintf('/api/events/%s/subscribe', LoadCoalitionEventData::EVENT_1_UUID), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => 'j.hey@en-marche-dev.fr',
        ]));

        $this->isSuccessful($this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}
