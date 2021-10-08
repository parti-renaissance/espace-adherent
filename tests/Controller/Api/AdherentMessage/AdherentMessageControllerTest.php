<?php

namespace Tests\App\Controller\Api\AdherentMessage;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCauseData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class AdherentMessageControllerTest extends AbstractWebCaseTest
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private const URL = '/api/v3/adherent_messages';

    public function testAsCauseAuthorICanCreateMessage()
    {
        $token = $this->getToken();

        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $token", 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'type' => 'coalitions',
                'label' => 'Label du message qui permet de le retrouver ds la liste des messages envoyés',
                'subject' => "L'objet du mail",
                'content' => '<table>...</table>',
                'json_content' => '{"foo": "bar", "items": [1, 2, true, "hello world"]}',
            ])
        );

        $response = $this->client->getResponse();
        $this->isSuccessful($response);

        $data = json_decode($response->getContent(), true);

        self::assertArrayHasKey('uuid', $data);

        $this->client->request(
            Request::METHOD_GET,
            self::URL.'/'.($uuid = $data['uuid']).'/content',
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $token"],
        );

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        self::assertSame([
            'subject' => 'L\'objet du mail',
            'content' => '<table>...</table>',
            'json_content' => '{"foo": "bar", "items": [1, 2, true, "hello world"]}',
        ], $data);

        $this->client->request(
            Request::METHOD_PUT,
            self::URL.'/'.$uuid.'/filter',
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $token", 'CONTENT_TYPE' => 'application/json'],
            json_encode(['cause' => LoadCauseData::CAUSE_6_UUID])
        );

        $this->assertResponseIsSuccessful();
    }

    private function getToken(): string
    {
        return $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'jacques.picard@en-marche.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );
    }
}
