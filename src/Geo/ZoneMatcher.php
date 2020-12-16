<?php

namespace App\Geo;

use App\Entity\Geo\City;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Geocoder\GeoPointInterface;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;

class ZoneMatcher
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ZoneRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $em, ZoneRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @return Zone[]
     */
    public function match(PostAddress $address): array
    {
        $zones = [];

        $isFrance = PostAddress::FRANCE === $address->getCountry();

        if ($isFrance) {
            // Borough or city
            $zones[] =
                $this->repository->findOneBy(['code' => $address->getInseeCode(), 'type' => Zone::BOROUGH]) ?:
                $this->repository->findOneBy(['code' => $address->getInseeCode(), 'type' => Zone::CITY]) ?:
                $this->matchPostalCode($address->getPostalCode())
            ;

            // Districts and cantons
            $zones = array_merge($zones, $this->matchGeoPoint(
                $address,
                [Zone::DISTRICT, Zone::CANTON]
            ));
        } else {
            // Foreign district
            $zones = array_merge($zones, $this->matchGeoPoint(
                $address,
                [Zone::FOREIGN_DISTRICT]
            ));

            // Country
            $zones[] = $this->repository->findOneBy([
                'code' => $address->getCountry(),
                'type' => Zone::COUNTRY,
            ]);
        }

        return array_values(array_filter(array_unique($zones)));
    }

    /**
     * @return Zone[]
     */
    private function matchGeoPoint(GeoPointInterface $geoPoint, array $types): array
    {
        $latitude = $geoPoint->getLatitude();
        $longitude = $geoPoint->getLongitude();
        if (!$latitude || !$longitude) {
            return [];
        }

        return $this->repository->findByCoordinatesAndTypes($latitude, $longitude, $types);
    }

    private function matchPostalCode(?string $postalCode): ?Zone
    {
        if (!$postalCode) {
            return null;
        }

        $cities = $this->em->getRepository(City::class)
            ->createQueryBuilder('c')
            ->where('c.postalCode LIKE :postal_code')
            ->setParameter(':postal_code', "%{$postalCode}%")
            ->getQuery()
            ->getResult()
        ;

        if (1 !== \count($cities)) {
            return null;
        }

        return $this->repository->findByZoneable($cities[0]);
    }
}
