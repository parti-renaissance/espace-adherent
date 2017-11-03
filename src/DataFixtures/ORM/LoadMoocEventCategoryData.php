<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MoocEventCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMoocEventCategoryData extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $category = new MoocEventCategory('Séance MOOC');

        $this->addReference('mooc-event-category', $category);

        $manager->persist($category);
        $manager->flush();
    }
}
