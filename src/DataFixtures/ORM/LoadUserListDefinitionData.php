<?php

namespace App\DataFixtures\ORM;

use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserListDefinitionData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        foreach (UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE_LABELS as $label) {
            $userListDefinition = new UserListDefinition(UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE, $label);
            $manager->persist($userListDefinition);
        }

        $manager->flush();
    }
}
