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
class ExportPhoningCampaignSurveyAnswersControllerTest extends AbstractApiTestCase
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

        $this->client->request(Request::METHOD_GET, '/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies.xlsx?scope=phoning_national_manager', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    #[DataProvider('provideReferents')]
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

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies.xlsx?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $content = $this->client->getInternalResponse()->getContent();

        $response = $this->client->getResponse();
        $this->isSuccessful($response);

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-sur-l-horizon-2030_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(4, $this->transformToArray($content));
    }

    public static function provideReferents(): iterable
    {
        yield ['referent@en-marche-dev.fr', 'president_departmental_assembly'];
        yield ['senateur@en-marche-dev.fr', 'delegated_08f40730-d807-4975-8773-69d8fae1da74'];
    }
}
