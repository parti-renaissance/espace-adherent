<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeoZoneData extends Fixture implements DependentFixtureInterface
{
    public static $zoneCache;

    public function load(ObjectManager $manager): void
    {
        /** @var Connection $conn */
        $conn = $manager->getConnection();
        $conn->executeStatement(file_get_contents(__DIR__.'/../../../dump/all-geo-zone.sql'));

        $votePlace = new Zone(Zone::VOTE_PLACE, 'BDV-TEST-1', 'Bureau de vote NICE 1');
        $votePlace->addParent(self::getZone($manager, 'zone_city_06088'));
        $manager->persist($votePlace);
        $this->setReference('zone_vote_place_nice_1', $votePlace);

        $votePlace = new Zone(Zone::VOTE_PLACE, 'BDV-TEST-2', 'Bureau de vote CLICHY 1');
        $votePlace->addParent(self::getZone($manager, 'zone_city_92024'));
        $votePlace->addParent(self::getZone($manager, 'zone_department_92'));
        $manager->persist($votePlace);
        $this->setReference('zone_vote_place_clichy_1', $votePlace);

        $manager->flush();
    }

    public static function getZoneReference(EntityManagerInterface $manager, string $reference): ?Zone
    {
        static::initZoneCache();

        return isset(static::$zoneCache[$reference]) ? $manager->getPartialReference(Zone::class, static::$zoneCache[$reference]) : null;
    }

    public static function getZone(EntityManagerInterface $manager, string $reference): ?Zone
    {
        if ($zoneRef = static::getZoneReference($manager, $reference)) {
            $manager->refresh($zoneRef);
        }

        return $zoneRef;
    }

    protected static function initZoneCache(): void
    {
        if (null === static::$zoneCache) {
            $file = fopen(__DIR__.'/../geo/geo-zones.csv', 'rb');
            $header = fgetcsv($file, 0, ';', escape: '\\');
            static::$zoneCache = [];

            while ($row = fgetcsv($file, 0, ';', escape: '\\')) {
                $row = array_combine($header, $row);
                static::$zoneCache[$row['zone']] = (int) $row['id'];
            }
        }
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoPolygonsData::class,
        ];
    }
}
