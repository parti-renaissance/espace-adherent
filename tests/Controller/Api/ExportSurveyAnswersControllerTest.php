<?php

declare(strict_types=1);

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
class ExportSurveyAnswersControllerTest extends AbstractApiTestCase
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

        $this->client->request(Request::METHOD_GET, '/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies.xlsx?scope=national', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    #[DataProvider('provideUsers')]
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

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/api/v3/surveys/4c3594d4-fb6f-4e25-ac2e-7ef81694ec47/replies.xlsx?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $content = $this->client->getInternalResponse()->getContent();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="les-enjeux-des-10-prochaines-annees_\d+_\d{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(7, $this->transformToArray($content));
    }

    public static function provideUsers(): iterable
    {
        yield ['deputy@en-marche-dev.fr', 'national'];
        yield ['referent-75-77@en-marche-dev.fr', 'president_departmental_assembly'];
        yield ['francis.brioul@yahoo.com', 'delegated_689757d2-dea5-49d1-95fe-281fc860ff77'];
    }
}
