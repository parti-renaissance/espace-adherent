<?php

namespace App\DataFixtures\ORM;

use App\Entity\DonatorIdentifier;
use Doctrine\Persistence\ObjectManager;

class LoadDonatorIdentifierData extends AbstractFixtures
{
    public function load(ObjectManager $manager)
    {
        $donationIdentifier = new DonatorIdentifier();
        $donationIdentifier->setIdentifier('000052');

        $manager->persist($donationIdentifier);
        $manager->flush();
    }
}
