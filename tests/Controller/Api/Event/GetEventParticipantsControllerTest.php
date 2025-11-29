<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class GetEventParticipantsControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testExportEventParticipantsInXls(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            Scope::JEMENGAGE_ADMIN,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5/participants.xlsx?scope=president_departmental_assembly', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->isSuccessful($response = $this->client->getResponse());
        $content = $this->client->getInternalResponse()->getContent();

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="nouvel-evenement-online_\d{4}-[\d]{2}-[\d]{2}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(5, $this->transformToArray($content));
    }
}
