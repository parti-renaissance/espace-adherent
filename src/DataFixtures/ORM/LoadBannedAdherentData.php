<?php

namespace App\DataFixtures\ORM;

use App\Entity\BannedAdherent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadBannedAdherentData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new BannedAdherent(Uuid::uuid4()));
        $manager->flush();
    }
}
