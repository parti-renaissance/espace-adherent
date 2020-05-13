<?php

namespace App\Repository;

use App\Entity\District;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, District::class);
    }

    /**
     * Finds district by coordinates of the point.
     *
     * @return District[]
     */
    public function findDistrictsByCoordinates($latitude, $longitude): array
    {
        return $this->createQueryBuilder('district')
            ->join('district.referentTag', 'referentTag')
            ->addSelect('referentTag')
            ->join('district.geoData', 'geoData')
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',:longitude,' ',:latitude,')')), geoData.geoShape) = 1")
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->getQuery()
            ->getResult()
        ;
    }
}
