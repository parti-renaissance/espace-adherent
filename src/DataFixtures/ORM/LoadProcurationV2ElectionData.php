<?php

namespace App\DataFixtures\ORM;

use App\Entity\ProcurationV2\Election;
use App\Entity\ProcurationV2\Round;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadProcurationV2ElectionData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $election = $this->createElection('Européennes 2024', 'europeennes-2024');
        $election->addRound($round = $this->createRound('Premier tour', '2024-06-09'));

        $manager->persist($election);
        $this->setReference('procuration-v2-round-1', $round);

        $manager->flush();
    }

    private function createElection(
        string $name,
        string $slug
    ): Election {
        $election = new Election();
        $election->name = $name;
        $election->slug = $slug;

        $election->requestTitle = $this->faker->text(30);
        $election->requestDescription = $this->faker->text();
        $election->requestLegal = $this->faker->text();
        $election->requestConfirmation = $this->faker->text();

        $election->proxyTitle = $this->faker->text(30);
        $election->proxyDescription = $this->faker->text();
        $election->proxyLegal = $this->faker->text();
        $election->proxyConfirmation = $this->faker->text();

        return $election;
    }

    private function createRound(
        string $name,
        string $date
    ): Round {
        $round = new Round();
        $round->name = $name;
        $round->description = $this->faker->text();
        $round->date = new \DateTimeImmutable($date);

        return $round;
    }
}
