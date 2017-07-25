<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Skill;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSkillData implements FixtureInterface
{
    const SKILLS = [
        'S001' => 'Software',
        'S002' => 'Analyze',
        'S003' => 'Mathématiques',
        'S004' => 'Statistique',
        'S005' => 'Ecriture médiatique',
        'S006' => 'Gestion des relations',
        'S007' => 'Culture de l’image',
        'S008' => 'Outils médias',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SKILLS as $name) {
            $manager->persist(new Skill($name));
        }

        $manager->flush();
    }
}
