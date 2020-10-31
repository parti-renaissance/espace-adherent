<?php

namespace App\DataFixtures\ORM;

use App\Entity\InstitutionalEventCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadInstitutionalEventCategoryData extends Fixture
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
