<?php

namespace App\DataFixtures\ORM;

use App\DataFixtures\AutoIncrementResetter;
use App\Entity\Jecoute\Region;
use App\Jecoute\RegionColorEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteRegionData extends Fixture
{
    public const REGION_1_UUID = '88275043-adb5-463a-8a62-5248fe7aacbf';
    public const REGION_2_UUID = 'c91391e9-4a08-4d14-8960-6c3508c1dddc';

    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'jecoute_region');

        $manager->persist($this->createRegion(
            self::REGION_1_UUID,
            'Normandie',
            '28',
            'Bienvenue en Normandie',
            'Description de la normandie',
            RegionColorEnum::RED,
            'region-logo.jpg',
            'region-banner.jpg',
            'https://en-marche.fr'
        ));

        $manager->persist($this->createRegion(
            self::REGION_2_UUID,
            'Hauts-de-France',
            '32',
            'Bienvenue en Hauts-de-France',
            'Description des Hauts-de-France',
            RegionColorEnum::GREEN,
            'region-logo.jpg'
        ));

        $manager->flush();
    }

    private function createRegion(
        string $uuid,
        string $name,
        string $code,
        string $subtitle,
        string $description,
        string $primaryColor,
        string $logo,
        string $banner = null,
        string $externalLink = null
    ): Region {
        return new Region(
            Uuid::fromString($uuid),
            $name,
            $code,
            $subtitle,
            $description,
            $primaryColor,
            $logo,
            $banner,
            $externalLink
        );
    }
}
