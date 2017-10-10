<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\BoardMember\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBoardMemberRoleData extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
    const BOARD_MEMBER_ROLES = [
        'BMR01' => 'Adhérent(e)s membre de la société civile',
        'BMR02' => 'Référent(e)s territoriaux',
        'BMR03' => 'Député(e)s nationaux',
        'BMR04' => 'Député(e)s européens',
        'BMR05' => 'Ministres',
        'BMR06' => 'Sénateurs(trices)',
        'BMR07' => 'Conseiller(e)s Consulaires',
        'BMR08' => 'Président(e) du groupe LaREM d\'un exécutif local',
        'BMR09' => 'Président(e) de région / département / assemblée territoriale',
        'BMR10' => 'Maires d\'une commune de moins de 50 000 habitants',
        'BMR11' => 'Maires d\'une commune de plus de 50 000 habitants',
        'BMR12' => 'Président(e) d\'établissement de coopération intercommunale EPCI < 100 000 habitants',
        'BMR13' => 'Président(e) d\'établissement de coopération intercommunale EPCI > 100 000 habitants',
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::BOARD_MEMBER_ROLES as $key => $name) {
            $role = new Role($name);
            $manager->persist($role);
            $this->addReference(strtolower($key), $role);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return -2;
    }
}
