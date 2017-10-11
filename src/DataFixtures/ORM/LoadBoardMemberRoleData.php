<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\BoardMember\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBoardMemberRoleData extends AbstractFixture implements FixtureInterface
{
    const BOARD_MEMBER_ROLES = [
        'adherent' => [
            'male' => 'Adhérent membre de la société civile',
            'female' => 'Adhérente membre de la société civile',
        ],
        'supervisor' => [
            'male' => 'Animateur de comités locaux',
            'female' => 'Animatrice de comités locaux',
        ],
        'referent' => [
            'male' => 'Référent territorial',
            'female' => 'Référente territoriale',
        ],
        'deputy' => [
            'male' => 'Députée national',
            'female' => 'Députée nationale',
        ],
        'european_deputy' => [
            'male' => 'Député européen',
            'female' => 'Députée européenne',
        ],
        'minister' => [
            'male' => 'Ministre',
            'female' => 'Ministre',
        ],
        'senator' => [
            'male' => 'Sénateur',
            'female' => 'Sénatrice',
        ],
        'consular' => [
            'male' => 'Conseiller Consulaire',
            'female' => 'Conseillère Consulaire',
        ],
        'president_larem' => [
            'male' => 'Président du groupe LaREM d\'un exécutif local',
            'female' => 'Présidente du groupe LaREM d\'un exécutif local',
        ],
        'president' => [
            'male' => 'Présidente de région / département / assemblée territoriale',
            'female' => 'Présidente de région / département / assemblée territoriale',
        ],
        'mayor_less' => [
            'male' => 'Maire d\'une commune de moins de 50 000 habitants',
            'female' => 'Maire d\'une commune de moins de 50 000 habitants',
        ],
        'mayor_more' => [
            'male' => 'Maire d\'une commune de plus de 50 000 habitants',
            'female' => 'Maire d\'une commune de plus de 50 000 habitants',
        ],
        'president_less' => [
            'male' => 'Président d\'établissement de coopération intercommunale EPCI < 100 000 habitants',
            'female' => 'Présidente d\'établissement de coopération intercommunale EPCI < 100 000 habitants',
        ],
        'president_more' => [
            'male' => 'Président d\'établissement de coopération intercommunale EPCI > 100 000 habitants',
            'female' => 'Présidente d\'établissement de coopération intercommunale EPCI > 100 000 habitants',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::BOARD_MEMBER_ROLES as $code => $names) {
            $role = new Role($code, $names['male'], $names['female']);
            $manager->persist($role);
            $this->addReference(strtolower($code), $role);
        }

        $manager->flush();
    }
}
