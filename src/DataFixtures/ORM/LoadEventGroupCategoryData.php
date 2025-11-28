<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Event\EventCategory;
use App\Entity\Event\EventGroupCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadEventGroupCategoryData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $eventGroupCategory0 = new EventGroupCategory('événement');
        $eventGroupCategory0->description = 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.';
        $this->addReference('event-group-category-0', $eventGroupCategory0);

        $eventGroupCategory1 = new EventGroupCategory('Atelier');
        $eventGroupCategory1->description = 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.';
        $this->addReference('event-group-category-1', $eventGroupCategory1);

        $eventGroupCategory2 = new EventGroupCategory('Conférence');
        $eventGroupCategory2->description = 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.';
        $this->addReference('event-group-category-2', $eventGroupCategory2);

        $eventGroupCategory3 = new EventGroupCategory('projets', EventCategory::DISABLED);
        $eventGroupCategory3->description = 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.';
        $this->addReference('event-group-category-3', $eventGroupCategory3);

        $eventGroupCategory4 = new EventGroupCategory('Évènements de campagne');
        $eventGroupCategory4->description = 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.';
        $this->addReference('event-group-category-4', $eventGroupCategory4);

        $manager->persist($eventGroupCategory0);
        $manager->persist($eventGroupCategory1);
        $manager->persist($eventGroupCategory2);
        $manager->persist($eventGroupCategory3);
        $manager->persist($eventGroupCategory4);

        $manager->flush();
    }
}
