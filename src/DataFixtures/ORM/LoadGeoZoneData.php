<?php

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadGeoZoneData extends Fixture implements DependentFixtureInterface
{
    public static $zoneCache;

    public function load(ObjectManager $manager): void
    {
        /** @var Connection $conn */
        $conn = $manager->getConnection();
        $conn->exec(file_get_contents(__DIR__.'/../../../dump/all-geo-zone.sql'));

        $votePlace = new Zone(Zone::VOTE_PLACE, 'BDV-TEST-1', 'Bureau de vote TEST 1');
        $votePlace->addParent($this->getZone($manager, 'zone_borough_75110'));
        $manager->persist($votePlace);

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
            $header = fgetcsv($file, 0, ';');
            static::$zoneCache = [];

            while ($row = fgetcsv($file, 0, ';')) {
                $row = array_combine($header, $row);
                static::$zoneCache[$row['zone']] = (int) $row['id'];
            }
        }
    }

    public function getDependencies()
    {
        return [
            LoadGeoPolygonsData::class,
        ];
    }
}
