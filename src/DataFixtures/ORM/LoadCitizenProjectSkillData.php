<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenProjectSkill;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenProjectSkillData extends AbstractFixture implements FixtureInterface
{
    const SKILLS = [
        'CPS001' => 'Software',
        'CPS002' => 'Analyze',
        'CPS003' => 'Mathématiques',
        'CPS004' => 'CPStatistique',
        'CPS005' => 'Ecriture médiatique',
        'CPS006' => 'Gestion des relations',
        'CPS007' => 'Culture de l\'image',
        'CPS008' => 'Outils médias',
        'CPS009' => 'Médecine',
        'CPS010' => 'Psychologie',
        'CPS011' => 'Animation d\'une équipe',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::SKILLS as $code => $name) {
            $skill = new CitizenProjectSkill($name);
            $manager->persist($skill);
            $this->addReference(strtolower($code), $skill);
        }
        $manager->flush();
    }
}
