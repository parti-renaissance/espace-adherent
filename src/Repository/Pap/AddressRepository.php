<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function findNear(float $latitude, float $longitude, int $zoom, int $limit = 300): array
    {
        $sql = <<<SQL
            select
                address.id,
                (6371 * 
                 ACOS(
                   COS(RADIANS(:latitude)) 
                 * COS(RADIANS(latitude)) 
                 * COS(RADIANS(longitude) - RADIANS(:longitude)) 
                 + SIN(RADIANS(:latitude)) 
                 * SIN(RADIANS(latitude))
                 )) as distance
            from pap_address AS address
            where offset_x
            between 2*(17-:zoom)*FLOOR((:longitude+180)/360*(1 << :zoom)-1)
                and 2*(17-:zoom)*FLOOR((:longitude+180)/360*(1 << :zoom)+1)
            and offset_y
                between 2*(17-:zoom)*floor((1.0-ln(tan(radians(:latitude))+1.0/cos(radians(:latitude)))/pi())/2.0*(1 << :zoom)-1)
                and 2*(17-:zoom)*floor((1.0-ln(tan(radians(:latitude))+1.0/cos(radians(:latitude)))/pi())/2.0*(1 << :zoom)+1)
            order by distance asc
            limit :limit
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

        $ids = array_column($result->fetchAllAssociative(), 'id');

        $qb = $this
            ->createQueryBuilder('address')
            ->select('address, building, building_block, floor')
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
            ->leftJoin('building.buildingBlocks', 'building_block')
            ->leftJoin('building_block.floors', 'floor')
            ->andWhere('address.id IN (:address_ids)')
            ->setParameter('address_ids', $ids)
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->orderBy('distance', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
