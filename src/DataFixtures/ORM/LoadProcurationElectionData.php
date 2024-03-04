<?php

namespace App\DataFixtures\ORM;

use App\Entity\Procuration\Election;
use App\Entity\Procuration\Round;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadProcurationElectionData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $election1 = $this->createElection('Européennes 2019', 'europeennes-2019');
        $election1->addRound($this->createRound('Premier tour', new \DateTimeImmutable('2024-06-09')));

        $manager->persist($election1);

        $manager->flush();
    }

    private function createElection(
        string $name,
        string $slug
    ): Election {
        $election = new Election();
        $election->name = $name;
        $election->slug = $slug;

        $election->requestTitle = $this->faker->text();
        $election->requestDescription = $this->faker->text();
        $election->requestConfirmation = $this->faker->text();
        $election->requestLegal = $this->faker->text();

        $election->proxyTitle = $this->faker->text();
        $election->proxyDescription = $this->faker->text();
        $election->proxyConfirmation = $this->faker->text();
        $election->proxyLegal = $this->faker->text();

        return $election;
    }

    private function createRound(
        string $name,
        \DateTimeInterface $date
    ): Round {
        $round = new Round();
        $round->name = $name;
        $round->date = $date;

        $round->description = $this->faker->text();

        return $round;
    }
}
