<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use App\Entity\Pap\Campaign;
use App\Entity\Pap\VotePlace;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Pap\VotePlace>
 */
class VotePlaceRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VotePlace::class);
    }

    public function findNear(float $latitude, float $longitude, int $limit = 300): array
    {
        $sql = <<<SQL
            SELECT
               vp.uuid,
               vp.latitude,
               vp.longitude,
               COUNT(ad.id) AS addresses,
               (6371 *
                    ACOS(
                    COS(RADIANS(:latitude))
                    * COS(RADIANS(vp.latitude))
                    * COS(RADIANS(vp.longitude) - RADIANS(:longitude))
                    + SIN(RADIANS(:latitude))
                    * SIN(RADIANS(vp.latitude))
                   )
               ) AS distance
            FROM pap_vote_place AS vp
            LEFT JOIN pap_address AS ad ON ad.vote_place_id = vp.id
            GROUP BY vp.id
            ORDER BY distance ASC
            LIMIT :limit
            SQL;

        $stmt = $this
            ->getEntityManager()
            ->getConnection()
            ->prepare($sql)
        ;

        $stmt->bindParam('latitude', $latitude);
        $stmt->bindParam('longitude', $longitude);
        $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);

        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

    public function withZones(QueryBuilder $queryBuilder, array $zones, string $alias): void
    {
        $this->withGeoZones(
            $zones,
            $queryBuilder,
            $alias,
            VotePlace::class,
            'vp2',
            'zone',
            'vpz2'
        );
    }

    /**
     * @return VotePlace[]
     */
    public function findAvailableForCampaign(Campaign $campaign, array $zones): array
    {
        $this->withZones(
            $queryBuilder = $this->createQueryBuilder($alias = 'vp'),
            $zones,
            $alias
        );

        $campaignConditions = [
            'campaign.beginAt < :finish_at',
            'campaign.finishAt > :begin_at',
        ];
        $params = [
            'begin_at' => $campaign->getBeginAt(),
            'finish_at' => $campaign->getFinishAt(),
        ];

        if ($campaign->getId()) {
            $campaignConditions[] = 'campaign.id != :campaign_id';
            $params['campaign_id'] = $campaign->getId();
        }

        $subQuery = $this->createQueryBuilder('vp_sub')
            ->select('DISTINCT vp_sub.id')
            ->innerJoin('vp_sub.campaigns', 'campaign', Join::WITH, implode(' AND ', $campaignConditions))
        ;

        $queryBuilder->andWhere($alias.'.id NOT IN('.$subQuery->getDQL().')');

        foreach ($params as $key => $value) {
            $queryBuilder->setParameter($key, $value);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return VotePlace[]
     */
    public function findByCampaign(Campaign $campaign): array
    {
        return $this->createQueryBuilder('vote_place')
            ->where(':campaign MEMBER OF vote_place.campaigns')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getResult()
        ;
    }
}
