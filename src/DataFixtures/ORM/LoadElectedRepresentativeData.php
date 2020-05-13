<?php

namespace App\DataFixtures\ORM;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use libphonenumber\PhoneNumber;

class LoadElectedRepresentativeData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $electedRepresentatives = [
            ['adherent-1', 'Michelle', 'DUFOUR', 'female', '1972-11-23', 1203084],
            [null, 'Delphine', 'BOUILLOUX', 'female', '1977-08-02', 1203080],
            [null, 'Daniel', 'BOULON', 'male', '1951-03-04', 694516],
            [null, 'Roger', 'BUET', 'male', '1952-04-21', 873399],
            [null, 'André', 'LLOBELL', 'male', '1951-11-03', 873404],
            [null, 'Sans', 'OFFICIELID', 'male', '1951-11-03', 873404],
        ];

        foreach ($electedRepresentatives as $data) {
            $electedRepresentative = new ElectedRepresentative(
                $data[1],
                $data[2],
                $data[3],
                new DateTime($data[4]),
                $data[5]
            );

            if (null !== $data[0]) {
                $electedRepresentative->setAdherent($this->getReference($data[0]));

                $phone = new PhoneNumber();
                $phone->setCountryCode('33');
                $phone->setNationalNumber('0999887766');
                $electedRepresentative->setContactPhone($phone);
            }

            $manager->persist($electedRepresentative);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
