<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Campaign;
use App\Repository\GeoZoneTrait;
use App\Repository\UuidEntityRepositoryTrait;
use App\Scope\ScopeVisibilityEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class CampaignRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait;
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    public function findCampaignsKpi(array $zones = []): array
    {
        $queryBuilder = $this->createQueryBuilder('campaign')
            ->select('COUNT(DISTINCT campaign.id) AS nb_campaigns')
            ->addSelect('COUNT(DISTINCT IF(campaign.finishAt >= :now OR campaign.finishAt IS NULL, campaign.id, null)) AS nb_ongoing_campaigns')
            ->addSelect('COUNT(campaignHistory.id) AS nb_visited_doors')
            ->addSelect('COUNT(IF(campaignHistory.createdAt >= :last_30d AND campaignHistory.createdAt <= :now, campaignHistory.id, null)) AS nb_visited_doors_last_30d')
            ->addSelect('COUNT(campaignHistory.dataSurvey) as nb_surveys')
            ->addSelect('COUNT(IF(dataSurvey.postedAt >= :last_30d AND dataSurvey.postedAt <= :now, dataSurvey.id, null)) as nb_surveys_last_30d')
            ->leftJoin(
                'campaign.campaignHistories',
                'campaignHistory',
                Join::WITH,
                'campaignHistory.door IS NOT NULL'
            )
            ->leftJoin('campaignHistory.dataSurvey', 'dataSurvey')
            ->setParameters([
                'now' => new \DateTime(),
                'last_30d' => new \DateTime('-30 days'),
            ])
        ;

        $this->applyKpiScopeVisibility($queryBuilder, $zones);

        return $queryBuilder
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function applyKpiScopeVisibility(QueryBuilder $queryBuilder, array $zones): void
    {
        $condition = $queryBuilder->expr()->orX();

        $condition->add('campaign.visibility = :visibility_national');
        $queryBuilder->setParameter('visibility_national', ScopeVisibilityEnum::NATIONAL);

        if (!empty($zones)) {
            $condition->add(
                $queryBuilder->expr()->andX(
                    'campaign.visibility = :visibility_local',
                    'zone IN (:zones) OR zone_parent IN (:zones)'
                )
            );

            $queryBuilder
                ->leftJoin('campaign.zones', 'zone')
                ->leftJoin('zone.parents', 'zone_parent')
                ->setParameter('visibility_local', ScopeVisibilityEnum::LOCAL)
                ->setParameter('zones', $zones)
            ;
        }

        $queryBuilder->andWhere($condition);
    }

    public function getActiveCampaignIds(): array
    {
        return array_map('intval', array_column($this->createQueryBuilder('campaign')
            ->select('campaign.id')
            ->where('campaign.beginAt < :now AND campaign.finishAt > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getScalarResult(), 'id'))
        ;
    }

    /**
     * @return Campaign[]
     */
    public function findUnassociatedCampaigns(\DateTime $startDate): array
    {
        return $this->createQueryBuilder('campaign')
            ->where('campaign.associated = :false')
            ->andWhere('campaign.beginAt <= :date AND campaign.finishAt > :date')
            ->setParameters([
                'date' => $startDate,
                'false' => false,
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /** @return Campaign[] */
    public function findCampaignsByVotePlaces(array $votePlaces, Campaign $excludedCampaign = null): array
    {
        $qb = $this->createQueryBuilder('campaign')
            ->innerJoin('campaign.votePlaces', 'votePlace', Join::WITH, 'votePlace IN (:vote_places)')
            ->setParameter('vote_places', $votePlaces)
        ;

        if ($excludedCampaign) {
            $this->withExcludedCampaign($qb, $excludedCampaign);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Campaign[] */
    public function findCampaignsByZones(array $zones, Campaign $excludedCampaign = null): array
    {
        $qb = $this->createQueryBuilder('campaign');
        $this->withGeoZones(
                $zones,
                $qb,
                'campaign',
                Campaign::class,
                'c2',
                'zones',
                'z2'
            )
        ;

        if ($excludedCampaign) {
            $this->withExcludedCampaign($qb, $excludedCampaign);
        }

        return $qb->getQuery()->getResult();
    }

    private function withExcludedCampaign(QueryBuilder $qb, Campaign $excludedCampaign): QueryBuilder
    {
        if ($excludedCampaign->getId()) {
            $qb
                ->andWhere('campaign != :excluded_campaign')
                ->setParameter('excluded_campaign', $excludedCampaign)
            ;
        }

        $qb
            ->andWhere('(campaign.beginAt < :finish_at AND campaign.finishAt > :begin_at)')
            ->setParameter('begin_at', $excludedCampaign->getBeginAt())
            ->setParameter('finish_at', $excludedCampaign->getFinishAt())
        ;

        return $qb;
    }
}
