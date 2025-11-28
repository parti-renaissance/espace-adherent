<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Pap;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\DataFixtures\ORM\LoadPapBuildingData;
use App\DataFixtures\ORM\LoadPapCampaignData;
use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingEvent;
use App\Entity\Pap\Campaign;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use App\Repository\Pap\BuildingEventRepository;
use App\Repository\Pap\BuildingRepository;
use App\Repository\Pap\CampaignRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class BuildingEventControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private ?BuildingEventRepository $buildingEventRepository;
    private ?CampaignRepository $campaignRepository;
    private ?BuildingRepository $buildingRepository;

    #[DataProvider('provideActions')]
    public function testCloseOpenBuilding(string $type, string $action, ?string $identifier = null): void
    {
        $campaignUuid = LoadPapCampaignData::CAMPAIGN_1_UUID;
        $buidingUuid = LoadPapBuildingData::BUILDING_01_UUID;
        $campaign = $this->campaignRepository->findOneByUuid($campaignUuid);
        $building = $this->buildingRepository->findOneByUuid($buidingUuid);
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_10_UUID,
            'MWFod6bOZb2mY3wLE=4THZGbOfHJvRHk8bHdtZP3BTr',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            'deputy@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(
            Request::METHOD_POST,
            \sprintf('/api/v3/pap/buildings/%s/events', $buidingUuid),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => "Bearer $accessToken",
            ],
            json_encode(array_merge([
                'action' => $action,
                'type' => $type,
                'campaign' => $campaignUuid,
            ], $identifier ? ['identifier' => $identifier] : []))
        );

        $this->assertResponseStatusCodeSame(201);

        $this->assertSame('"OK"', $this->client->getResponse()->getContent());

        /** @var BuildingEvent $buildingEvent */
        $buildingEvent = $this->getLastBuildingEvent($campaign, $building);

        $this->assertNotNull($buildingEvent);
        $this->assertSame($building->getId(), $buildingEvent->getBuilding()->getId());
        $this->assertSame($campaign->getId(), $buildingEvent->getCampaign()->getId());
        $this->assertSame($action, $buildingEvent->getAction());
        $this->assertSame($identifier, $buildingEvent->getIdentifier());
        $this->assertSame($type, $buildingEvent->getType());
    }

    public static function provideActions(): array
    {
        return [
            ['building_block', 'open', 'A'],
            ['building_block', 'close', 'A'],
            ['floor', 'open', 'A-0'],
            ['floor', 'close', 'A-0'],
            ['building', 'open'],
            ['building', 'close'],
        ];
    }

    private function getLastBuildingEvent(Campaign $campaign, Building $building): ?BuildingEvent
    {
        return $this->buildingEventRepository->createQueryBuilder('event')
            ->where('event.campaign = :campaign AND event.building = :building')
            ->setParameters([
                'campaign' => $campaign,
                'building' => $building,
            ])
            ->orderBy('event.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildingEventRepository = $this->getBuildingEventRepository();
        $this->campaignRepository = $this->getPapCampaignRepository();
        $this->buildingRepository = $this->getBuildingRepository();
    }

    protected function tearDown(): void
    {
        $this->buildingEventRepository = null;
        $this->campaignRepository = null;
        $this->buildingRepository = null;

        parent::tearDown();
    }
}
