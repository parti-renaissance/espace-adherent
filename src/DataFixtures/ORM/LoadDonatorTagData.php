<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\DonatorTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDonatorTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new DonatorTag('Très grand donateur', '#32cd32'));
        $manager->persist(new DonatorTag('Grand donateur', '#ff4500'));
        $manager->persist(new DonatorTag('Résident étranger', '#00bfff'));

        $manager->flush();
    }
}
