<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class EventsControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testCannotExportCauseEventRegistrationsIfNotEventAuthor(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'gisele-berthoux@caramail.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/events/ef62870c-6d42-47b6-91ea-f454d473adf8/export-registrations', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testExportCauseEventRegistrations(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'jacques.picard@en-marche.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/v3/events/ef62870c-6d42-47b6-91ea-f454d473adf8/export-registrations', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="[\d-]{10}_Evenement_Inscrits.xls"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>N° d\'enregistrement</th><th>Prénom</th><th>Nom</th><th>Code postal</th><th>Date d\'inscription</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>Jacques</td><td>Picard</td><td></td><td>', $responseContent);
        $this->assertStringContainsString('<td>Francis</td><td>Brioul</td><td></td><td>', $responseContent);
    }

    public function testExportCauseEventRegistrationsOfACancelledEvent(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'jacques.picard@en-marche.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/v3/events/8047158c-8a3b-4c30-86fe-5e0148567051/export-registrations', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="[\d-]{10}_Evenement_Inscrits.xls"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>N° d\'enregistrement</th><th>Prénom</th><th>Nom</th><th>Code postal</th><th>Date d\'inscription</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>Jacques</td><td>Picard</td><td></td><td>', $responseContent);
        $this->assertStringContainsString('<td>Élodie</td><td>Dutemps</td><td></td><td>', $responseContent);
        $this->assertStringContainsString('<td>Pierre</td><td>Kiroule</td><td></td><td>', $responseContent);
    }
}
