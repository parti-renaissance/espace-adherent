<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\EventCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEventCategoryData extends Fixture
{
    public const LEGACY_EVENT_CATEGORIES = [
        'CE001' => 'Kiosque',
        'CE002' => 'Réunion d\'équipe',
        'CE003' => 'Conférence-débat',
        'CE004' => 'Porte-à-porte',
        'CE005' => 'Atelier du programme',
        'CE006' => 'Tractage',
        'CE007' => 'Convivialité',
        'CE008' => 'Action ciblée',
        'CE009' => 'Événement innovant',
        'CE010' => 'Marche',
        'CE011' => 'Support party',
    ];

    public const HIDDEN_CATEGORY_NAME = 'Catégorie masquée';

    public function load(ObjectManager $manager)
    {
        foreach (self::LEGACY_EVENT_CATEGORIES as $name) {
            $manager->persist(new EventCategory($name));
        }

        $eventGroupCategory1 = $this->getReference('event-group-category-1');
        $eventGroupCategory2 = $this->getReference('event-group-category-2');

        $eventCategory1 = new EventCategory('ancrage local');
        $eventCategory1->setEventGroupCategory($eventGroupCategory1);

        $eventCategory2 = new EventCategory('projets citoyens');
        $eventCategory2->setEventGroupCategory($eventGroupCategory1);

        $eventCategory3 = new EventCategory('Un An');
        $eventCategory3->setEventGroupCategory($eventGroupCategory1);

        $eventCategory4 = new EventCategory('Débat');
        $eventCategory4->setEventGroupCategory($eventGroupCategory2);

        $manager->persist($eventCategory1);
        $manager->persist($eventCategory2);
        $manager->persist($eventCategory3);
        $manager->persist($eventCategory4);

        $manager->persist(new EventCategory(self::HIDDEN_CATEGORY_NAME, EventCategory::DISABLED));

        $manager->flush();
    }
    public function getDependencies()
    {
        return [
            LoadEventGroupCategoryData::class
        ];
    }

}
