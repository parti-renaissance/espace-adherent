<?php

namespace App\DataFixtures\ORM;

use App\Entity\DonatorIdentifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDonatorIdentifierData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $donationIdentifier = new DonatorIdentifier();
        $donationIdentifier->setIdentifier('000055');

        $manager->persist($donationIdentifier);
        $manager->flush();
    }
}
