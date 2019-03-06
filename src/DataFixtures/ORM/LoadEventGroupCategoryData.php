<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\EventCategory;
use AppBundle\Entity\EventGroupCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventGroupCategoryData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $eventGroupCategory1 = new EventGroupCategory('Atelier');
        $this->addReference('event-group-category-1', $eventGroupCategory1);
        $eventGroupCategory2 = new EventGroupCategory('Conférence');
        $this->addReference('event-group-category-2', $eventGroupCategory2);

        $eventGroupCategory3 = new EventGroupCategory('projets', EventCategory::DISABLED);
        $this->addReference('event-group-category-3', $eventGroupCategory3);

        $manager->persist($eventGroupCategory1);
        $manager->persist($eventGroupCategory2);
        $manager->persist($eventGroupCategory3);

        $manager->flush();
    }
}
