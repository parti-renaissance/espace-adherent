<?php

namespace App\DataFixtures\ORM;

use App\Entity\Pap\Address;
use App\Entity\Pap\Voter;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapAddressData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $address = $this->createAddress(
            '55',
            'Rue du Rocher',
            '75108',
            'Paris 8ème',
            66380,
            45080,
            48.878708,
            2.319111
        );
        $address->addVoter($this->createVoter('John', 'Doe', Genders::MALE, '-30 years'));
        $address->addVoter($this->createVoter('Jane', 'Doe', Genders::FEMALE, '-29 years'));
        $manager->persist($address);

        $address = $this->createAddress(
            '65',
            'Rue du Rocher',
            '75108',
            'Paris 8ème',
            66380,
            45080,
            48.879078,
            2.318631
        );
        $address->addVoter($this->createVoter('Jack', 'Doe', Genders::MALE, '-55 years'));
        $manager->persist($address);

        $address = $this->createAddress(
            '67',
            'Rue du Rocher',
            '75108',
            'Paris 8ème',
            66380,
            45079,
            48.879246,
            2.318427
        );
        $address->addVoter($this->createVoter('Mickaël', 'Doe', Genders::MALE, '-44 years'));
        $address->addVoter($this->createVoter('Mickaëla', 'Doe', Genders::FEMALE, '-45 years'));
        $address->addVoter($this->createVoter('Mickaël Jr', 'Doe', Genders::MALE, '-22 years'));
        $manager->persist($address);

        $address = $this->createAddress(
            '70',
            'Rue du Rocher',
            '75108',
            'Paris 8ème',
            66380,
            45080,
            48.879166,
            2.318761
        );
        $address->addVoter($this->createVoter('Patrick', 'Simpson Jones', Genders::MALE, '-70 years'));
        $manager->persist($address);

        $manager->flush();
    }

    private function createAddress(
        string $number,
        string $street,
        string $inseeCode,
        string $cityName,
        int $offsetX,
        int $offsetY,
        float $latitude,
        float $longitude
    ): Address {
        return new Address(
            Uuid::uuid4(),
            $number,
            $street,
            $inseeCode,
            $cityName,
            $offsetX,
            $offsetY,
            $latitude,
            $longitude
        );
    }

    private function createVoter(string $firstName, string $lastName, string $gender, string $birthdate): Voter
    {
        return new Voter(
            Uuid::uuid4(),
            $firstName,
            $lastName,
            $gender,
            new \DateTime($birthdate)
        );
    }
}
