<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Reporting\UserRoleHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadUserRoleHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent5 = $this->getReference('adherent-5');
        $adherent13 = $this->getReference('adherent-13');
        $admin = $this->getReference('administrator-2');

        $manager->persist($this->createHistory(
            $adherent5,
            UserRoleHistory::ACTION_ADD,
            'deputy',
            $admin
        ));

        $manager->persist($this->createHistory(
            $adherent5,
            UserRoleHistory::ACTION_REMOVE,
            'deputy',
            $admin
        ));

        $manager->persist($this->createHistory(
            $adherent13,
            UserRoleHistory::ACTION_ADD,
            'deputy',
            null,
            $adherent5
        ));

        $manager->flush();
    }

    public function createHistory(
        Adherent $adherent,
        string $action,
        string $role,
        ?Administrator $adminAuthor = null,
        ?Adherent $userAuthor = null,
    ): UserRoleHistory {
        return new UserRoleHistory($adherent, $action, $role, $adminAuthor, $userAuthor);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
