<?php

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
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5/participants.xlsx?scope=referent', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="nouvel-evenement-online_\d{4}-[\d]{2}-[\d]{2}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>Prénom</th><th>Nom</th><th>Email</th><th>Labels</th><th>Code postal</th><th>Téléphone</th><th>Date d\'inscription</th></tr>', $responseContent);
        $this->assertStringContainsString('<tr><td>Referent</td><td>Referent</td><td>referent@en-marche-dev.fr</td><td></td><td>77000</td><td>+33 6 73 65 43 49</td>', $responseContent);
        $this->assertStringContainsString('<tr><td>Francis</td><td>Brioul</td><td>francis.brioul@yahoo.com</td><td></td><td>77000</td><td>+33 6 73 65 43 49</td>', $responseContent);
        $this->assertStringContainsString('<tr><td>Simple</td><td>User</td><td>simple-user@example.ch</td><td></td><td>8057</td><td></td>', $responseContent);
        $this->assertStringContainsString('<tr><td>Marie</td><td>CLAIRE</td><td>marie.claire@test.com</td><td></td><td></td><td></td>', $responseContent);
    }
}
