<?php

namespace App\Repository\Pap;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Pap\Building;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\CampaignHistory;
use App\Repository\UuidEntityRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CampaignHistoryRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;

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
            'door'))
        ;
    }

    public function countDoorsForBuilding(Building $building): int
    {
        return (int) $this
            ->createQueryBuilder('campaignHistory')
            ->select('COUNT(DISTINCT CONCAT(campaignHistory.floor, \'-\', campaignHistory.door))')
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
            ->groupBy('adherent.id')
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('campaignHistory.createdAt', 'DESC')
            ->addOrderBy('adherent.id', 'ASC')
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
            ->addSelect('COUNT(DISTINCT CONCAT(campaignHistory.buildingBlock, \'-\', campaignHistory.floor, \'-\', campaignHistory.door)) AS nb_visited_doors')
            ->addSelect('SUM(IF(campaignHistory.dataSurvey IS NOT NULL, 1, 0)) as nb_surveys')
            ->innerJoin('campaignHistory.questioner', 'adherent')
            ->where('campaignHistory.campaign = :campaign')
            ->setParameters([
                'campaign' => $campaign,
            ])
        ;
    }

    public function findDepartmentRanking(Campaign $campaign, int $limit = 10): array
    {
        return $this->createDepartmentRankingQueryBuilder($campaign)
            ->groupBy('zone.id')
            ->orderBy('nb_surveys', 'DESC')
            ->addOrderBy('campaignHistory.createdAt', 'DESC')
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
            ->addSelect('COUNT(DISTINCT CONCAT(campaignHistory.buildingBlock, \'-\', campaignHistory.floor, \'-\', campaignHistory.door)) AS nb_visited_doors')
            ->addSelect('SUM(IF(campaignHistory.dataSurvey IS NOT NULL, 1, 0)) as nb_surveys')
            ->innerJoin('campaignHistory.building', 'building')
            ->innerJoin('building.address', 'address')
            ->innerJoin(
                Zone::class,
                'zone',
                Join::WITH,
                '(address.dptCode = zone.code AND zone.type = :dpt_type AND zone.code != :dpt_paris)'
                        .'OR (address.inseeCode = zone.code AND zone.type = :borough_type AND zone.name LIKE :paris)'
            )
            ->where('campaignHistory.campaign = :campaign')
            ->setParameters([
                'campaign' => $campaign,
                'dpt_type' => Zone::DEPARTMENT,
                'borough_type' => Zone::BOROUGH,
                'paris' => 'Paris %',
                'dpt_paris' => '75',
            ])
        ;
    }
}
