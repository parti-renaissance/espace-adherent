<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenInitiativeCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenInitiativeCategoryData implements FixtureInterface
{
    const CITIZEN_INITIATIVE_CATEGORIES = [
        'CIC001' => 'Nature et environnement',
        'CIC002' => 'Education et citoyenneté',
        'CIC003' => 'Culture et loisirs',
        'CIC004' => 'Lien intergénérationnel',
        'CIC005' => 'Aide aux personnes en difficulté',
        'CIC006' => 'Bien-être et santé',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_INITIATIVE_CATEGORIES as $name) {
            $manager->persist(new CitizenInitiativeCategory($name));
        }

        $manager->flush();
    }
}
