<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\DepartmentSite\DepartmentSite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDepartmentSiteData extends Fixture implements DependentFixtureInterface
{
    public const UUID1 = '51e507e5-3d7c-4f08-b05d-b7cb45e960d3';

    public function load(ObjectManager $manager): void
    {
        $zone92 = LoadGeoZoneData::getZoneReference($manager, 'zone_department_92');
        $manager->refresh($zone92);

        $site = new DepartmentSite(Uuid::fromString(self::UUID1));
        $site->setContent(file_get_contents(__DIR__.'/../unlayer/content.md'));
        $site->setJsonContent(file_get_contents(__DIR__.'/../unlayer/json_content.json'));
        $site->setZone($zone92);

        $manager->persist($site);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
