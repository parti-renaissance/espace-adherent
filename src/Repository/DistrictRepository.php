<?php

namespace AppBundle\Repository;

use AppBundle\Entity\District;
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
            ->where("ST_Within(ST_GeomFromText(CONCAT('POINT(',:longitude,' ',:latitude,')')), geoData.geoShape) = true")
            ->setParameters([
                'longitude' => $longitude,
                'latitude' => $latitude,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
