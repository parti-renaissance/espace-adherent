<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDonationData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-3');

        $donation1 = new Donation(
            Uuid::uuid4(),
            5000,
            $adherent->getGender(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress(),
            $adherent->getPostAddress(),
            $adherent->getPhone(),
            '0.0.0.0'
        );

        $donation2 = new Donation(
            Uuid::uuid4(),
            7000,
            $adherent->getGender(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress(),
            $adherent->getPostAddress(),
            $adherent->getPhone(),
            '0.0.0.0'
        );

        $donation1->finish([
            'result' => '00000',
            'authorization' => 'test',
        ]);
        $donation2->finish([
            'result' => '00000',
            'authorization' => 'test',
        ]);

        $reflectDonation = new \ReflectionObject($donation2);
        $reflectDonationAt = $reflectDonation->getProperty('donatedAt');
        $reflectDonationAt->setAccessible(true);
        $reflectDonationAt->setValue($donation2, new \DateTime('-1 day'));
        $reflectDonationAt->setAccessible(false);

        $manager->persist($donation1);
        $manager->persist($donation2);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
