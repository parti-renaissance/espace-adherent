<?php

namespace App\Repository\Pap;

use App\Entity\Pap\VotePlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VotePlaceRepository extends ServiceEntityRepository
{
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
}
