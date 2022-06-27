<?php

namespace App\DataFixtures\ORM;

use App\Entity\RepublicanSilence;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadRepublicanSilenceData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $entity = new RepublicanSilence();
        $entity->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'));
        $entity->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_91'));
        $entity->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_93'));
        $entity->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_country_SG'));
        $entity->setBeginAt(new \DateTime('-10 days'));
        $entity->setFinishAt(new \DateTime('+10 days'));

        $manager->persist($entity);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadReferentTagData::class,
            LoadGeoZoneData::class,
        ];
    }
}
