<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
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
                *,
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

        $query = $this->getEntityManager()->createNativeQuery($sql, new ResultSetMapping());

        $query->setParameter('latitude', $latitude, 'float');
        $query->setParameter('longitude', $longitude, 'float');
        $query->setParameter('zoom', $zoom, 'integer');
        $query->setParameter('limit', $limit, 'integer');

        return $query->getResult('PapAddressHydrator');
    }
}
