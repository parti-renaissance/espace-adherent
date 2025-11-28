<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\InvalidEmailAddress;
use App\InvalidEmailAddress\HashGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadInvalidEmailAddressData extends Fixture
{
    private HashGenerator $hashGenerator;

    public function __construct(HashGenerator $hashGenerator)
    {
        $this->hashGenerator = $hashGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist(new InvalidEmailAddress($this->hashGenerator->generate('invalid-email@en-marche-dev.code')));

        $manager->flush();
    }
}
