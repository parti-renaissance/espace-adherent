<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\EventCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenProjectCategoryData extends AbstractFixture
{
    const CITIZEN_PROJECT_CATEGORIES = [
        'CPC001' => 'Nature et Environnement',
        'CPC002' => 'Education, culture et citoyenneté',
        'CPC003' => 'Culture',
        'CPC004' => 'Lien social et aide aux personnes en difficulté',
        'CPC005' => 'Santé',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_PROJECT_CATEGORIES as $code => $name) {
            $category = new CitizenProjectCategory($name, EventCategory::ENABLED);
            $manager->persist($category);
            $this->addReference(strtolower($code), $category);
        }

        $manager->flush();
    }
}
