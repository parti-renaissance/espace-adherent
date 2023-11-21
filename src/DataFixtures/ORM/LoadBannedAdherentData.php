<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\BannedAdherent;
use App\Entity\PostAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadBannedAdherentData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $adherent = Adherent::createBlank('male', 'test', 'test', 'FR', PostAddress::createEmptyAddress(), 'disabled-email@test.com', null, new \DateTime('-18 years'));

        $manager->persist(BannedAdherent::createFromAdherent($adherent));
        $manager->flush();
    }
}
