<?php

declare(strict_types=1);

namespace App\Geo;

use App\Address\AddressInterface;
use App\Entity\Geo\City;
use App\Entity\Geo\Zone;
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

    /**
     * @var Zone|null
     */
    private $fde;

    public function __construct(EntityManagerInterface $em, ZoneRepository $repository)
    {
        $this->em = $em;
        $this->repository = $repository;
    }

    /**
     * @return Zone[]
     */
    public function match(AddressInterface $address): array
    {
        $zones = [];

        $isFrance = AddressInterface::FRANCE === $address->getCountry();

        if ($isFrance) {
            // Borough or city
            $zones[] =
                $this->repository->findOneBy(['code' => str_pad((string) $address->getInseeCode(), 5, '0', \STR_PAD_LEFT), 'type' => [Zone::BOROUGH, Zone::CITY]])
                    ?: ($this->matchPostalCode($address->getPostalCode()
                        ?: $this->matchPostalCode($address->getPostalCode(), $address->getCityName())));

            // Districts and cantons
            if ($address instanceof GeoPointInterface) {
                $departments = $this->extractDepartments($zones);
                $zones = array_merge($zones, $this->matchGeoPoint($address, [Zone::DISTRICT, Zone::CANTON], $departments));
            }
        } else {
            // Foreign district
            if ($address instanceof GeoPointInterface) {
                $this->fde = $this->fde ?: $this->fde = $this->repository->findOneBy([
                    'code' => Zone::FDE_CODE,
                    'type' => Zone::CUSTOM,
                ]);

                $zones = array_merge($zones, $this->matchGeoPoint($address, [Zone::FOREIGN_DISTRICT, Zone::CUSTOM], [$this->fde]));
            }

            // Country
            $zones[] = $this->repository->findOneBy([
                'code' => $address->getCountry(),
                'type' => Zone::COUNTRY,
            ]);
        }

        return array_values(array_filter(array_unique($zones)));
    }

    public function matchPostalCode(?string $postalCode, ?string $cityName = null): ?Zone
    {
        if (!$postalCode) {
            return null;
        }

        $qb = $this->em->getRepository(City::class)
            ->createQueryBuilder('c')
            ->where('(c.postalCode LIKE :postal_code_1 OR c.postalCode LIKE :postal_code_2)')
            ->setParameter('postal_code_1', $postalCode.'%')
            ->setParameter('postal_code_2', '%,'.$postalCode.'%')
        ;

        if ($cityName) {
            $qb
                ->andWhere('c.name = :city_name')
                ->setParameter('city_name', $cityName)
            ;
        }

        $cities = $qb->getQuery()->getResult();

        if (1 !== \count($cities)) {
            return null;
        }

        return $this->repository->findByZoneable($cities[0]);
    }

    /**
     * @param Zone[] $parents
     *
     * @return Zone[]
     */
    private function matchGeoPoint(GeoPointInterface $geoPoint, array $types, array $parents): array
    {
        $latitude = $geoPoint->getLatitude();
        $longitude = $geoPoint->getLongitude();
        if (!$latitude || !$longitude) {
            return [];
        }

        return $this->repository->findByCoordinatesAndTypes($latitude, $longitude, $types, $parents);
    }

    /**
     * @param Zone[] $zones
     *
     * @return Zone[]
     */
    private function extractDepartments(array $zones): array
    {
        $zones = array_filter($zones);
        if (!$zones) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_merge(
                ...array_map(static function (Zone $zone): array {
                    return $zone->getParents();
                }, $zones)
            ),
            static function (Zone $zone): bool {
                return Zone::DEPARTMENT === $zone->getType();
            }
        )));
    }

    public function flattenZones(array $zones, array $types = []): array
    {
        $zones = array_unique(array_merge(...array_map(
            fn (Zone $zone) => $zone->getWithParents($types),
            array_filter($zones, fn (Zone $zone) => !$types || \in_array($zone->getType(), $types))
        )));

        return array_values($zones);
    }
}
