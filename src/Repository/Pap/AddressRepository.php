<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use App\Entity\Pap\Campaign;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class AddressRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /** @return Address[] */
    public function findNear(
        float $latitude,
        float $longitude,
        ?float $latitudeDelta,
        ?float $longitudeDelta,
        int $limit = 300,
        array $votePlaces = []
    ): array {
        if (null !== $latitudeDelta && null !== $longitudeDelta) {
            $sql = <<<SQL
        SELECT address.id
            FROM pap_address AS address
            INNER JOIN pap_vote_place pvp ON pvp.id = address.vote_place_id
            WHERE 
                address.offset_x BETWEEN 
                    FLOOR((:longitude - :delta_longitude / 2 + 180) / 360 * (1 << 17)) 
                    AND FLOOR((:longitude + :delta_longitude / 2 + 180) / 360 * (1 << 17))
                AND address.offset_y BETWEEN 
                    FLOOR((1.0 - LN(TAN(RADIANS(:latitude + :delta_latitude / 2)) + 1.0 / COS(RADIANS(:latitude + :delta_latitude / 2))) / PI()) / 2.0 * (1 << 17))
                    AND FLOOR((1.0 - LN(TAN(RADIANS(:latitude - :delta_latitude / 2)) + 1.0 / COS(RADIANS(:latitude - :delta_latitude / 2))) / PI()) / 2.0 * (1 << 17))
                And address.vote_place_id IN (
SQL;
        } else {
            $sql = <<<SQL
        SELECT address.id
            FROM pap_address AS address
            INNER JOIN pap_vote_place pvp ON pvp.id = address.vote_place_id
            WHERE 
                address.offset_x BETWEEN 
                 FLOOR((:longitude + 180) / 360 * (1 << 17)) - (1 << greatest((17 - 15), 0))
                    AND FLOOR((:longitude + 180) / 360 * (1 << 17)) + (1 << greatest((17 - 15), 0))
                AND address.offset_y BETWEEN 
                    FLOOR((1.0 - LN(TAN(RADIANS(:latitude)) + 1.0 / COS(RADIANS(:latitude))) / PI()) / 2.0 * (1 << 17)) - (1 << greatest((17 - 15), 0))
                    AND FLOOR((1.0 - LN(TAN(RADIANS(:latitude)) + 1.0 / COS(RADIANS(:latitude))) / PI()) / 2.0 * (1 << 17)) + (1 << greatest((17 - 15), 0))
                And address.vote_place_id IN (
SQL;
        }

        $sql .= implode(', ', $votePlaces);
        $sql .= ') ';
        $sql .= <<<SQL
            ORDER BY 
                (6371 * 
                     ACOS(
                       COS(RADIANS(:latitude)) 
                     * COS(RADIANS(address.latitude)) 
                     * COS(RADIANS(address.longitude) - RADIANS(:longitude)) 
                     + SIN(RADIANS(:latitude)) 
                     * SIN(RADIANS(address.latitude))
                 ))
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

        if (null !== $latitudeDelta && null !== $longitudeDelta) {
            $stmt->bindParam('delta_latitude', $latitudeDelta);
            $stmt->bindParam('delta_longitude', $longitudeDelta);
        }

        $result = $stmt->executeQuery();

        $qb = $this
            ->createQueryBuilder('address')
            ->select('address', 'building', 'stats')
            ->addSelect('
                (6371 * 
                ACOS(
                    COS(RADIANS(:latitude)) 
                    * COS(RADIANS(address.latitude)) 
                    * COS(RADIANS(address.longitude) - RADIANS(:longitude)) 
                    + SIN(RADIANS(:latitude)) 
                    * SIN(RADIANS(address.latitude))
                )) as HIDDEN distance
            ')
            ->leftJoin('address.building', 'building')
            ->leftJoin('building.statistics', 'stats', Join::WITH, 'stats.campaign = building.currentCampaign')
            ->andWhere('address.id IN (:address_ids)')
            ->setParameter('address_ids', array_keys($result->fetchAllAssociativeIndexed()))
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->orderBy('distance', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function countByPapCampaign(Campaign $campaign): int
    {
        if ($campaign->isNationalVisibility()) {
            return (int) $this->createQueryBuilder('address')
                ->select('COUNT(1)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        $qb = $this->createQueryBuilder('address');
        $qb = $this->withGeoZones(
            [$campaign->getZone()],
            $qb,
            'address',
            Address::class,
            'a2',
            'zones',
            'z2'
        );

        return (int) $qb
            ->select('COUNT(DISTINCT address.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countVotersByPapCampaign(Campaign $campaign): int
    {
        if ($campaign->isNationalVisibility()) {
            return (int) $this->createQueryBuilder('address')
                ->select('SUM(address.votersCount)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        $qb = $this->createQueryBuilder('address');
        $qb = $this->withGeoZones(
            [$campaign->getZone()],
            $qb,
            'address',
            Address::class,
            'a2',
            'zones',
            'z2'
        );

        return (int) $qb
            ->select('SUM(address.votersCount)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
