<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Pap\Address;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\CampaignHistory;
use App\Pap\CampaignHistoryStatusEnum;
use App\Repository\GeoZoneTrait;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CampaignHistoryRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CampaignHistory::class);
    }

    public function findDoorsForFloor(Building $building, string $buildingBlock, int $floor): array
    {
        return array_unique(array_column(
            $this
                ->createQueryBuilder('campaignHistory')
                ->select('campaignHistory.door')
                ->where('campaignHistory.building = :building')
                ->andWhere('campaignHistory.buildingBlock = :buildingBlock AND campaignHistory.floor = :floor')
                ->setParameters([
                    'building' => $building,
                    'buildingBlock' => $buildingBlock,
                    'floor' => $floor,
                ])
                ->getQuery()
                ->getArrayResult(),
            'door'));
    }

    public function countDoorsForBuilding(Building $building): int
    {
        return (int) $this
            ->createQueryBuilder('campaignHistory')
            ->select('COUNT(DISTINCT CONCAT_WS(\'-\', campaignHistory.buildingBlock, campaignHistory.floor, campaignHistory.door))')
            ->where('campaignHistory.building = :building')
            ->setParameter('building', $building)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findLastFor(Building $building, Campaign $campaign): ?CampaignHistory
    {
        return $this
            ->createQueryBuilder('campaignHistory')
            ->where('campaignHistory.building = :building AND campaignHistory.campaign = :campaign')
            ->setParameters([
                'building' => $building,
                'campaign' => $campaign,
            ])
            ->orderBy('campaignHistory.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAdherentRanking(Campaign $campaign, int $limit = 10): array
    {
        return $this->createAdherentRankingQueryBuilder($campaign)
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('adherent.firstName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRankingForAdherent(Campaign $campaign, Adherent $adherent): array
    {
        return $this->createAdherentRankingQueryBuilder($campaign)
            ->andWhere('adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createAdherentRankingQueryBuilder(Campaign $campaign): QueryBuilder
    {
        return $this->createQueryBuilder('campaignHistory')
            ->select('adherent.id, adherent.firstName, adherent.lastName')
            ->addSelect('COUNT(DISTINCT CONCAT_WS(\'-\', adherent.id, building.id, campaignHistory.buildingBlock, campaignHistory.floor, campaignHistory.door)) AS nb_visited_doors')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->innerJoin('campaignHistory.questioner', 'adherent')
            ->innerJoin('campaignHistory.building', 'building')
            ->where('campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->groupBy('adherent.id')
        ;
    }

    public function findDepartmentRanking(Campaign $campaign, int $limit = 10): array
    {
        return $this->createDepartmentRankingQueryBuilder($campaign)
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('zone.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRankingForDepartment(Campaign $campaign, Zone $department): array
    {
        return $this->createDepartmentRankingQueryBuilder($campaign)
            ->andWhere('zone = :dpt')
            ->setParameter('dpt', $department)
            ->getQuery()
            ->getResult()
        ;
    }

    private function createDepartmentRankingQueryBuilder(Campaign $campaign): QueryBuilder
    {
        return $this->createQueryBuilder('campaignHistory')
            ->select('zone.id, zone.name')
            ->addSelect('COUNT(DISTINCT CONCAT_WS(\'-\', zone.id, building.id, campaignHistory.buildingBlock, campaignHistory.floor, campaignHistory.door)) AS nb_visited_doors')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->innerJoin('campaignHistory.building', 'building')
            ->innerJoin('building.address', 'address')
            ->innerJoin('address.zones', 'zone', Join::WITH, 'zone.type IN(:zone_types)')
            ->where('campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->setParameter('zone_types', [Zone::DEPARTMENT, Zone::BOROUGH])
            ->groupBy('zone.id')
        ;
    }

    public function findHistoryForBuilding(Building $building, Campaign $campaign): array
    {
        return $this->createQueryBuilder('campaignHistory')
            ->addSelect('adherent')
            ->innerJoin('campaignHistory.questioner', 'adherent')
            ->where('campaignHistory.building = :building AND campaignHistory.campaign = :campaign')
            ->setParameters([
                'building' => $building,
                'campaign' => $campaign,
            ])
            ->orderBy('campaignHistory.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCampaignAverageVisitTime(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('ABS(AVG(TIMESTAMPDIFF(SECOND, campaignHistory.finishAt, campaignHistory.beginAt))) AS average_visit_time')
            ->andWhere('campaignHistory.campaign = :campaign AND campaignHistory.finishAt IS NOT NULL')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countCollectedContacts(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1) AS nb_collected_contacts')
            ->andWhere('campaignHistory.campaign = :campaign AND campaignHistory.emailAddress IS NOT NULL AND campaignHistory.emailAddress != \'\'')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countVisitedDoors(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1)')
            ->andWhere('campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countOpenDoors(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1)')
            ->andWhere('campaignHistory.campaign = :campaign AND campaignHistory.status IN (:open_door)')
            ->setParameter('campaign', $campaign)
            ->setParameter('open_door', CampaignHistoryStatusEnum::OPEN_DOOR_STATUS)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countCampaignHistoriesWithDataSurvey(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1)')
            ->andWhere('campaignHistory.dataSurvey IS NOT NULL AND campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countToJoinByCampaign(Campaign $campaign, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1)')
            ->andWhere('campaignHistory.toJoin = :true AND campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->setParameter('true', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countByCampaignAndStatus(Campaign $campaign, string $status, array $zones = []): int
    {
        return (int) ($zones ? $this->createQueryBuilderWithGeoZonesCondition($zones) : $this->createQueryBuilder('campaignHistory'))
            ->select('COUNT(1)')
            ->andWhere('campaignHistory.status = :status AND campaignHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function createQueryBuilderWithGeoZonesCondition(array $zones): QueryBuilder
    {
        $qb = $this->createQueryBuilder('campaignHistory')
            ->innerJoin('campaignHistory.building', 'building')
            ->innerJoin('building.address', 'address')
        ;

        return $this->withGeoZones(
            $zones,
            $qb,
            'address',
            Address::class,
            'adr2',
            'zones',
            'zone2'
        );
    }
}
