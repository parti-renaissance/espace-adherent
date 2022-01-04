<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class GetEventParticipantsControllerTest extends WebTestCase
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
        $this->client->request(Request::METHOD_GET, '/api/v3/events/5cab27a7-dbb3-4347-9781-566dad1b9eb5/participants.xls?scope=referent', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="inscrits_a_l_evenement_[\d-]{10}-nouvel-evenement-online_[\d]{14}.xls"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>Date d\'inscription</th><th>Prénom</th><th>Nom</th><th>Code postal</th></tr>', $responseContent);
        $this->assertStringContainsString('</td><td>Referent</td><td>Referent</td><td>77000</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>Francis</td><td>Brioul</td><td>77000</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>Simple</td><td>User</td><td>8057</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>Marie</td><td>CLAIRE</td><td></td></tr></table></body></html>', $responseContent);
    }
}
