<?php

namespace AppBundle\Repository;

use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, District::class);
    }

    /**
     * Finds referent tag for district by coordinates of the point.
     */
    public function findDistrictReferentTagByCoordinates($latitude, $longitude): ?ReferentTag
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(ReferentTag::class, 'tag')
            ->select('tag')
            ->innerJoin(District::class, 'district', Join::WITH, 'district.referentTag = tag')
            ->innerJoin('district.geoData', 'geoData')
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',:longitude,' ',:latitude,')')), geoData.geoShape) = 1")
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
