<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\InstitutionalEventCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadInstitutionalEventCategoryData extends AbstractFixture
{
    const INSTITUTIONAL_EVENT_CATEGORIES = [
        'category-1' => 'Comité politique',
        'category-2' => 'Réunion publique',
        'category-3' => "Réunion de l'équipe départementale",
        'category-4' => 'Réunion des animateurs locaux',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::INSTITUTIONAL_EVENT_CATEGORIES as $code => $name) {
            $institutionalEventCategory = new InstitutionalEventCategory($name);

            $this->addReference('institutional-event-'.$code, $institutionalEventCategory);
            $manager->persist($institutionalEventCategory);
        }

        $manager->flush();
    }
}
