<?php

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class ExportPapCampaignSurveyAnswersControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testCannotExportPapCampaignRepliesIfNotAuthorized(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'benjyd@aol.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=pap_national_manager', [], [], [
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ]
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testExportPapCampaignRepliesInXlsByPapNationalManager(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'deputy@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=pap_national_manager', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>ID</th><th>Nom Prénom de l\'auteur</th><th>Posté le</th><th>Nom</th><th>Prénom</th><th>Code postal de l\'immeuble</th><th>Longitude</th><th>Latitude</th><th>A votre avis quels seront les enjeux des 10 prochaines années?</th><th>L\'écologie est selon vous, importante pour :</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Nouvelles technologies</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Les ressources énergétiques</td><td>L\'aspect financier, La préservation de l\'environnement</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Vie publique, répartition des pouvoirs et démocratie</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td></tr></table></body></html>', $responseContent);
    }

    public function testExportPapCampaignRepliesInXlsByReferentWithNoRepliesInManagedZones(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(Request::METHOD_GET, '/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=referent', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertSame('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head><body><table></table></body></html>', $responseContent);
    }

    #[DataProvider('provideReferents')]
    public function testExportPapCampaignRepliesInXlsByReferentWithRepliesInManagedZones(
        string $email,
        string $scope
    ): void {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            $email,
            LoadAdherentData::DEFAULT_PASSWORD
        );

        ob_start();
        $this->client->request(
            Request::METHOD_GET,
            sprintf('/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>ID</th><th>Nom Prénom de l\'auteur</th><th>Posté le</th><th>Nom</th><th>Prénom</th><th>Code postal de l\'immeuble</th><th>Longitude</th><th>Latitude</th><th>A votre avis quels seront les enjeux des 10 prochaines années?</th><th>L\'écologie est selon vous, importante pour :</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Nouvelles technologies</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Les ressources énergétiques</td><td>L\'aspect financier, La préservation de l\'environnement</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>75008</td><td>2.318427</td><td>48.879246</td><td>Vie publique, répartition des pouvoirs et démocratie</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td></tr></table></body></html>', $responseContent);
    }

    public static function provideReferents(): iterable
    {
        yield ['referent-75-77@en-marche-dev.fr', 'referent'];
        yield ['francis.brioul@yahoo.com', 'delegated_689757d2-dea5-49d1-95fe-281fc860ff77'];
    }
}
