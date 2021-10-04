<?php

namespace App\DataFixtures\ORM;

use App\Entity\Instance\AdherentInstanceQuality;
use App\Instance\InstanceQualityEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadAdherentInstanceQualityData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent = 41;

        foreach (InstanceQualityEnum::values() as $quality) {
            $manager->persist(new AdherentInstanceQuality(
                $this->getReference('adherent-'.($adherent++)),
                $this->getReference('instance-quality-'.$quality)
            ));
        }

        $manager->persist(new AdherentInstanceQuality(
            $this->getReference('adherent-41'),
            $this->getReference('instance-quality-custom_quality')
        ));

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadInstanceQualityData::class,
        ];
    }
}
