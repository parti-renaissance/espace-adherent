<?php

namespace App\DataFixturesPgsql;

use App\EntityPgsql\Address;
use Doctrine\Persistence\ObjectManager;

class AddressFixtures extends AbstractPgsqlFixtures
{
    public function load(ObjectManager $manager)
    {
        $manager->persist($this->create('2', 'Rue de la Paix'));
        $manager->persist($this->create('2 bis', 'Rue de la Paix'));

        $manager->flush();
    }

    private function create(string $number, string $street): Address
    {
        $address = new Address();
        $address->setNumber($number);
        $address->setStreet($street);

        return $address;
    }
}
