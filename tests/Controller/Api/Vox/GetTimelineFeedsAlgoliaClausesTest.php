<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Vox;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Adherent;
use App\JeMengage\Timeline\DataProvider;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;

/**
 * Characterization of the Algolia clause strings built by GetTimelineFeedsController: the exact
 * `filters`/`tagFilters` arrays sent to DataProvider are frozen in golden files for contrasted
 * fixture users. Purpose: the AudienceContext refactor (plan phase 8) must not change a single
 * clause. The ranker is pointed at the invalid-payload host so the controller takes the Algolia
 * fallback; DataProvider is replaced by a spy capturing its arguments.
 *
 * Record mode: a missing golden file is written from the CURRENT code then the test is marked
 * incomplete — delete a golden file to re-record it intentionally (e.g. after a fixtures change).
 */
#[Group('functional')]
#[Group('api')]
class GetTimelineFeedsAlgoliaClausesTest extends AbstractApiTestCase
{
    use ApiControllerTestTrait;

    private const string ENDPOINT = '/api/v3/je-mengage/timeline_feeds';
    private const string RANKER_INVALID_URL = 'https://ranker-invalid.timeline.test';
    // department 75 (fixtures), resolved by the `zone` query param.
    private const string ZONE_DEPT_75_UUID = 'e3efe563-906e-11eb-a875-0242ac150002';
    private const string COMMITTEE_UUID = '515a56c0-bde8-56ef-b90c-4745b1c93818';

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->disableReboot();
        $this->manager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->manager->getConnection()->rollBack();
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = '';

        parent::tearDown();
    }

    public function testRichUserClausesAreStable(): void
    {
        // Tags, committee membership, Paris zones, active mandates.
        $this->assertClausesMatchGolden('jacques.picard@en-marche.fr', '', 'rich_user');
    }

    public function testPoorUserClausesAreStable(): void
    {
        $this->assertClausesMatchGolden('president-ad@renaissance-dev.fr', '', 'poor_user');
    }

    public function testCumulatedQueryParamsClausesAreStable(): void
    {
        $this->assertClausesMatchGolden(
            'jacques.picard@en-marche.fr',
            '?zone='.self::ZONE_DEPT_75_UUID.'&committee='.self::COMMITTEE_UUID.'&instance=assembly',
            'query_params'
        );
    }

    private function assertClausesMatchGolden(string $email, string $query, string $goldenName): void
    {
        $token = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            $email,
            LoadAdherentData::DEFAULT_PASSWORD,
        );

        $captured = null;
        $dataProvider = $this->createMock(DataProvider::class);
        $dataProvider
            ->expects(self::once())
            ->method('findItems')
            ->with(self::isInstanceOf(Adherent::class), 0, self::anything(), self::anything())
            ->willReturnCallback(static function (Adherent $user, int $page, array $filters, array $tagFilters) use (&$captured): array {
                $captured = ['filters' => $filters, 'tagFilters' => $tagFilters];

                return ['hits' => [], 'nbHits' => 0, 'page' => 0, 'nbPages' => 0, 'hitsPerPage' => 20];
            });
        static::getContainer()->set(DataProvider::class, $dataProvider);

        // Invalid-payload ranker host: getItems throws, the controller takes the Algolia fallback
        // (the candidate set is never empty here: fixture national rows exist).
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = self::RANKER_INVALID_URL;

        $this->client->request(Request::METHOD_GET, self::ENDPOINT.$query, [], [], [
            'HTTP_AUTHORIZATION' => "Bearer $token",
        ]);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $response->getContent());
        self::assertNotNull($captured, 'The Algolia fallback was not taken.');

        $goldenPath = __DIR__.'/Fixtures/timeline_algolia_clauses_'.$goldenName.'.json';
        if (!file_exists($goldenPath)) {
            @mkdir(\dirname($goldenPath), 0o755, true);
            file_put_contents($goldenPath, json_encode($captured, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES)."\n");
            self::markTestIncomplete('Golden recorded at '.$goldenPath.' — re-run to assert.');
        }

        self::assertSame(json_decode(file_get_contents($goldenPath), true), $captured);
    }
}
