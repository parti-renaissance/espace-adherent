<?php

namespace App\DataFixtures\ORM;

use App\Entity\Instance\InstanceQuality;
use App\Instance\InstanceQualityEnum;
use App\Instance\InstanceQualityScopeEnum;
use Doctrine\Persistence\ObjectManager;

class LoadInstanceQualityData extends AbstractFixtures
{
    public function load(ObjectManager $manager)
    {
        foreach (InstanceQualityEnum::toArray() as $quality) {
            $manager->persist($object = new InstanceQuality($quality, [InstanceQualityScopeEnum::NATIONAL_COUNCIL], false));
            $this->setReference('instance-quality-'.$quality, $object);
        }

        $manager->persist($object = new InstanceQuality($quality = 'custom_quality', [InstanceQualityScopeEnum::NATIONAL_COUNCIL], true));
        $this->setReference('instance-quality-'.$quality, $object);

        $manager->persist($object = new InstanceQuality($quality = 'president_jam', [InstanceQualityScopeEnum::NATIONAL_COUNCIL], true));
        $this->setReference('instance-quality-'.$quality, $object);

        $manager->flush();
    }
}
