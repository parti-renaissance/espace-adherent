<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCitizenActionData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class SubscribeAsAdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testConnectedUserCanSubscribeOnEvent()
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_11_UUID,
            'Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ',
            GrantTypeEnum::PASSWORD,
            null,
            'carl999@example.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_POST, sprintf('/api/v3/events/%s/subscribe', LoadCitizenActionData::CITIZEN_ACTION_1_UUID), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->isSuccessful($this->client->getResponse());

        $this->client->request(Request::METHOD_POST, sprintf('/api/v3/events/%s/subscribe', LoadCitizenActionData::CITIZEN_ACTION_1_UUID), [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertStatusCode(400, $this->client);
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
