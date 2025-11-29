<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\DonatorIdentifier;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDonatorIdentifierData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $donationIdentifier = new DonatorIdentifier();
        $donationIdentifier->setIdentifier('000056');

        $manager->persist($donationIdentifier);
        $manager->flush();
    }
}
