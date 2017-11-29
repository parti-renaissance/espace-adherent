<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenProjectCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenProjectCategoryData extends AbstractFixture implements FixtureInterface
{
    const CITIZEN_PROJECT_CATEGORIES = [
        'CPC001' => 'Éducation, culture et citoyenneté',
        'CPC002' => 'Emploi et formation professionnelle',
        'CPC003' => 'Lien social et aide aux personnes en difficulté',
        'CPC004' => 'Lutte contre les discriminations',
        'CPC005' => 'Numérique, innovation et entrepreneuriat',
        'CPC006' => 'Transition écologique et solidaire',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_PROJECT_CATEGORIES as $code => $name) {
            $category = new CitizenProjectCategory($name);
            $manager->persist($category);
            $this->addReference(strtolower($code), $category);
        }

        $manager->flush();
    }
}
