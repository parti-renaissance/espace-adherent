<?php

namespace App\Repository;

use App\Entity\District;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DistrictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    public function findDistrictCountryCode(string $code): ?District
    {
        return $this->createQueryBuilder('district')
            ->join('district.referentTag', 'referentTag')
            ->addSelect('referentTag')
            ->where('FIND_IN_SET(:country_code, district.countries) > 0')
            ->setParameter('country_code', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
