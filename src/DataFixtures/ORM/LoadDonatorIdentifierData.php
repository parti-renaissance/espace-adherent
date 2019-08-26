<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\DonatorIdentifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDonatorIdentifierData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $donationIdentifier = new DonatorIdentifier();
        $donationIdentifier->setIdentifier('000052');

        $manager->persist($donationIdentifier);
        $manager->flush();
    }
}
