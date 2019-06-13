<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ApplicationRequest\ApplicationRequestTag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadApplicationRequestTagData extends AbstractFixture implements FixtureInterface
{
    private const TAGS = [1 => 'Tag 1', 2 => 'Tag 2', 3 => 'Tag 3', 4 => 'Tag 4'];

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
