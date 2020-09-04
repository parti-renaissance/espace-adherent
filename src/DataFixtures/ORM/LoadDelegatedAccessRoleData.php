<?php

namespace App\DataFixtures\ORM;

use App\Entity\MyTeam\DelegatedAccessGroup;
use App\Entity\MyTeam\DelegatedAccessRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDelegatedAccessRoleData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $group = new DelegatedAccessGroup();
        $group->setName('Responsables territoriaux');

        $role1 = new DelegatedAccessRole();
        $role1->setType('referent');
        $role1->setName('Responsable Mobilisation');
        $role1->setGroup($group);

        $this->setReference('referent-responsable-mobilisation', $role1);
        $manager->persist($role1);

        $role2 = new DelegatedAccessRole();
        $role2->setType('referent');
        $role2->setName('Responsable Logistique');
        $role2->setGroup($group);

        $this->setReference('referent-responsable-logistique', $role2);
        $manager->persist($role2);

        $role3 = new DelegatedAccessRole();
        $role3->setType('deputy');
        $role3->setName('Responsable communication');

        $this->setReference('deputy-responsable-communication', $role3);
        $manager->persist($role3);

        $role4 = new DelegatedAccessRole();
        $role4->setType('senator');
        $role4->setName('Responsable phoning');

        $this->setReference('senator-responsable-phoning', $role4);
        $manager->persist($role4);

        $manager->flush();
    }
}
