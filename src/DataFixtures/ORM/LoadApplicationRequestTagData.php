<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\ApplicationRequestTag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApplicationRequestTagData extends AbstractFixture
{
    private const TAGS = ['Tag 1', 'Tag 2', 'Tag 3', 'Tag 4'];

    public function load(ObjectManager $manager)
    {
        foreach (self::TAGS as $index => $name) {
            $applicationRequestTag = new ApplicationRequestTag($name);
            $manager->persist($applicationRequestTag);
            $this->addReference('application-request-tag-'.$index, $applicationRequestTag);
        }

        $manager->flush();
    }
}
