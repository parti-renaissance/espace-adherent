<?php

namespace App\DataFixtures\ORM;

use App\Entity\BannedAdherent;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadBannedAdherentData extends AbstractFixtures
{
    public function load(ObjectManager $manager)
    {
        $adherentBanned = new BannedAdherent(Uuid::fromString(LoadAdherentData::ADHERENT_14_UUID));
        $manager->persist($adherentBanned);
        $manager->flush();
    }
}
