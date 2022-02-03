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
class ExportPhoningCampaignSurveyAnswersControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testCannotExportPhoningCampaignRepliesIfNotAuthorized(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'benjyd@aol.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies.xls?scope=phoning_national_manager', [], [], [
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ]
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    /** @dataProvider provideReferents */
    public function testExportPhoningCampaignRepliesInXls(string $email, string $scope): void
    {
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
            sprintf('/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies.xls?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-sur-l-horizon-2030_Replies_[\d]{14}.xls"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>ID</th><th>Nom Prénom de l\'auteur</th><th>Posté le</th><th>Nom</th><th>Prénom</th><th>A votre avis quels seront les enjeux des 10 prochaines années?</th><th>L\'écologie est selon vous, importante pour :</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>Fa40ke</td><td>Adherent 40</td><td>l\'écologie sera le sujet le plus important</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td>', $responseContent);
        $this->assertStringContainsString('<td>Fa34ke</td><td>Adherent 34</td><td>le pouvoir d\'achat</td><td>L\'aspect financier, La préservation de l\'environnement</td>', $responseContent);
    }

    public function provideReferents(): iterable
    {
        yield ['referent@en-marche-dev.fr', 'referent'];
        yield ['senateur@en-marche-dev.fr', 'delegated_08f40730-d807-4975-8773-69d8fae1da74'];
    }
}
