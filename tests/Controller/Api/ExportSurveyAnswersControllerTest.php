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
class ExportSurveyAnswersControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    public function testCannotExportRepliesIfNotAuthorized(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'benjyd@aol.com',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies.xls?scope=national', [], [], [
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ]
        );

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    /** @dataProvider provideUsers */
    public function testExportSurveyRepliesInXls(string $email, string $scope): void
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
            sprintf('/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies.xls?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $responseContent = ob_get_clean();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="les-enjeux-des-10-prochaines-annees_3_[\d]{14}.xls"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertStringContainsString('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content="https://github.com/sonata-project/exporter"></head>', $responseContent);
        $this->assertStringContainsString('<body><table><tr><th>ID</th><th>Nom Prénom de l\'auteur</th><th>Posté le</th><th>Nom</th><th>Prénom</th><th>Code postal</th><th>Tranche d\'age</th><th>Genre</th><th>Profession</th><th>Code postal de l\'immeuble</th><th>Longitude</th><th>Latitude</th><th>A votre avis quels seront les enjeux des 10 prochaines années?</th><th>L\'écologie est selon vous, importante pour :</th></tr>', $responseContent);
        $this->assertStringContainsString('<td>Nouvelles technologies</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>Les ressources énergétiques</td><td>L\'aspect financier, La préservation de l\'environnement</td></tr>', $responseContent);
        $this->assertStringContainsString('<td>Vie publique, répartition des pouvoirs et démocratie</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td>', $responseContent);
        $this->assertStringContainsString('<td>l\'écologie sera le sujet le plus important</td><td>L\'héritage laissé aux générations futures, Le bien-être sanitaire</td>', $responseContent);
        $this->assertStringContainsString('<td>le pouvoir d\'achat</td><td>L\'aspect financier, La préservation de l\'environnement</td>', $responseContent);
    }

    public function provideUsers(): iterable
    {
        yield ['deputy@en-marche-dev.fr', 'national'];
        yield ['referent-75-77@en-marche-dev.fr', 'referent'];
        yield ['francis.brioul@yahoo.com', 'delegated_689757d2-dea5-49d1-95fe-281fc860ff77'];
    }
}
