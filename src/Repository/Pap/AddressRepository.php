<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use App\Entity\Pap\Campaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /** @return Address[] */
    public function findNear(float $latitude, float $longitude, int $zoom, int $limit = 300): array
    {
        $sql = <<<SQL
            SELECT address.id
            FROM pap_address AS address
            WHERE 
                address.offset_x BETWEEN 
                    2 * (17 - :zoom) * FLOOR((:longitude + 180) / 360 * (1 << :zoom) - 1)
                    AND 2 * (17 - :zoom) * FLOOR((:longitude + 180) / 360 * (1 << :zoom) + 1)
                AND address.offset_y BETWEEN 
                    2 * (17 - :zoom) * FLOOR((1.0 - LN(TAN(RADIANS(:latitude)) + 1.0 / COS(RADIANS(:latitude))) / PI()) / 2.0 * (1 << :zoom) - 1)
                    AND 2 * (17 - :zoom) * FLOOR((1.0 - LN(TAN(RADIANS(:latitude)) + 1.0 / COS(RADIANS(:latitude))) / PI()) / 2.0 * (1 << :zoom) + 1)
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
        $stmt->bindParam('zoom', $zoom, \PDO::PARAM_INT);
        $stmt->bindParam('limit', $limit, \PDO::PARAM_INT);

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
        return (int) $this->createQueryBuilder('address')
            ->select('COUNT(1)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countVotersByPapCampaign(Campaign $campaign): int
    {
        return (int) $this->createQueryBuilder('address')
            ->select('SUM(address.votersCount)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
