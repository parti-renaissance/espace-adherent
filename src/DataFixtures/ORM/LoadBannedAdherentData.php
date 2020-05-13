<?php

namespace App\DataFixtures\ORM;

use App\Entity\BannedAdherent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadBannedAdherentData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $adherentBanned = new BannedAdherent(Uuid::fromString(LoadAdherentData::ADHERENT_14_UUID));
        $manager->persist($adherentBanned);
        $manager->flush();
    }
}
