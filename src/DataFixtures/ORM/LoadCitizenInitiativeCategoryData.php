<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenInitiativeCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenInitiativeCategoryData implements FixtureInterface
{
    const CITIZEN_INITIATIVE_CATEGORIES = [
        'CIC001' => 'Kiosque (IC)',
        'CIC002' => 'Réunion d\'équipe (IC)',
        'CIC003' => 'Conférence-débat (IC)',
        'CIC004' => 'Porte-à-porte (IC)',
        'CIC005' => 'Atelier du programme (IC)',
        'CIC006' => 'Tractage (IC)',
        'CIC007' => 'Convivialité (IC)',
        'CIC008' => 'Action ciblée (IC)',
        'CIC009' => 'Événement innovant (IC)',
        'CIC010' => 'Marche (IC)',
        'CIC011' => 'Support party (IC)',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_INITIATIVE_CATEGORIES as $name) {
            $manager->persist(new CitizenInitiativeCategory($name));
        }

        $manager->flush();
    }
}
