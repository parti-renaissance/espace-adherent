<?php

namespace App\DataFixtures\ORM;

use App\Entity\Instance\InstanceQuality;
use App\Instance\InstanceQualityEnum;
use App\Instance\InstanceQualityScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadInstanceQualityData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach (InstanceQualityEnum::toArray() as $quality) {
            $manager->persist($object = new InstanceQuality($quality, [InstanceQualityScopeEnum::NATIONAL_COUNCIL]));
            $this->setReference('instance-quality-'.$quality, $object);
        }

        $manager->flush();
    }
}
