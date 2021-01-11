<?php

namespace App\Geo;

use App\Address\AddressInterface;
use App\Entity\Geo\City;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Geocoder\GeoPointInterface;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;

class ZoneMatcher
{
    private const FDE_CODE = 'FDE';
    private const FDE_TYPE = Zone::CUSTOM;

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

        $isFrance = PostAddress::FRANCE === $address->getCountry();

        if ($isFrance) {
            // Borough or city
            $zones[] =
                $this->repository->findOneBy(['code' => $inseeCode = str_pad($address->getInseeCode(), 5, '0', \STR_PAD_LEFT), 'type' => Zone::BOROUGH]) ?:
                $this->repository->findOneBy(['code' => $inseeCode, 'type' => Zone::CITY]) ?:
                $this->matchPostalCode($address->getPostalCode())
            ;

            // Districts and cantons
            if ($address instanceof GeoPointInterface) {
                $departments = $this->extractDepartments($zones);
                $zones = array_merge($zones, $this->matchGeoPoint($address, [Zone::DISTRICT, Zone::CANTON], $departments));
            }
        } else {
            // Foreign district
            if ($address instanceof GeoPointInterface) {
                $this->fde = $this->fde ?: $this->fde = $this->repository->findOneBy([
                    'code' => self::FDE_CODE,
                    'type' => self::FDE_TYPE,
                ]);

                $zones = array_merge($zones, $this->matchGeoPoint($address, [Zone::FOREIGN_DISTRICT], [$this->fde]));
            }

            // Country
            $zones[] = $this->repository->findOneBy([
                'code' => $address->getCountry(),
                'type' => Zone::COUNTRY,
            ]);
        }

        return array_values(array_filter(array_unique($zones)));
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
}
