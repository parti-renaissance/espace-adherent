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
        ]);

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

        $this->client->request(Request::METHOD_GET, '/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=pap_national_manager', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $content = $this->client->getInternalResponse()->getContent();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(4, $this->transformToArray($content));
    }

    public function testExportPapCampaignRepliesInXlsByReferentWithNoRepliesInManagedZones(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'president-ad@renaissance-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, '/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=president_departmental_assembly', [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $accessToken",
        ]);
        $content = $this->client->getInternalResponse()->getContent();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(1, $this->transformToArray($content));
    }

    #[DataProvider('provideReferents')]
    public function testExportPapCampaignRepliesInXlsByReferentWithRepliesInManagedZones(
        string $email,
        string $scope,
    ): void {
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
            \sprintf('/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/replies.xlsx?scope=%s', $scope),
            [],
            [],
            ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]
        );
        $content = $this->client->getInternalResponse()->getContent();

        $this->isSuccessful($response = $this->client->getResponse());

        self::assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        self::assertMatchesRegularExpression(
            '/^attachment; filename="campagne-de-10-jours-suivants_Replies_[\d]{14}.xlsx"$/',
            $response->headers->get('Content-Disposition')
        );

        $this->assertCount(4, $this->transformToArray($content));
    }

    public static function provideReferents(): iterable
    {
        yield ['referent-75-77@en-marche-dev.fr', 'president_departmental_assembly'];
        yield ['francis.brioul@yahoo.com', 'delegated_689757d2-dea5-49d1-95fe-281fc860ff77'];
    }
}
