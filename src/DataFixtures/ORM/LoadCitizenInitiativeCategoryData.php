<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenInitiativeCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenInitiativeCategoryData implements FixtureInterface
{
    const CITIZEN_INITIATIVE_CATEGORIES = [
        'CIC001' => 'Kiosque',
        'CIC002' => 'Réunion d\'équipe',
        'CIC003' => 'Conférence-débat',
        'CIC004' => 'Porte-à-porte',
        'CIC005' => 'Atelier du programme',
        'CIC006' => 'Tractage',
        'CIC007' => 'Convivialité',
        'CIC008' => 'Action ciblée',
        'CIC009' => 'Événement innovant',
        'CIC010' => 'Marche',
        'CIC011' => 'Support party',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::CITIZEN_INITIATIVE_CATEGORIES as $name) {
            $manager->persist(new CitizenInitiativeCategory($name));
        }

        $manager->flush();
    }
}
