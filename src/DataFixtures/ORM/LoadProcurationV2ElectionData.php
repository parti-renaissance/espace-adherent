<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\ProcurationV2\Election;
use App\Entity\ProcurationV2\Round;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class LoadProcurationV2ElectionData extends Fixture
{
    public const UUID_LEGISLATIVES_ROUND_1 = 'f048f91c-0d04-4211-9c78-515644fdc100';
    public const UUID_LEGISLATIVES_ROUND_2 = 'edf49758-c047-472d-9a98-4d24fbc58190';

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $election = $this->createElection('Européennes 2024', 'europeennes-2024');
        $election->addRound($round = $this->createRound('Premier tour', '2024-06-09'));

        $manager->persist($election);
        $this->setReference('procuration-v2-europeennes-2024-round-1', $round);

        $election2 = $this->createElection('Législatives 2024', 'legislatives-2024');
        $election2->addRound($round1 = $this->createRound(
            'Premier tour',
            (new \DateTime('+14 days'))->format('Y-m-d'),
            Uuid::fromString(self::UUID_LEGISLATIVES_ROUND_1)
        ));
        $election2->addRound($round2 = $this->createRound(
            'Deuxième tour',
            (new \DateTime('+21 days'))->format('Y-m-d'),
            Uuid::fromString(self::UUID_LEGISLATIVES_ROUND_2)
        ));

        $manager->persist($election2);
        $this->setReference('procuration-v2-legislatives-2024-round-1', $round1);
        $this->setReference('procuration-v2-legislatives-2024-round-2', $round2);

        $manager->flush();
    }

    private function createElection(
        string $name,
        string $slug,
    ): Election {
        $election = new Election();
        $election->name = $name;
        $election->slug = $slug;

        $election->requestTitle = '<span class="text-[#00AEEF]">
                Le 9 juin,
            </span>
            <br>
            Je vote par<br>procuration';
        $election->requestDescription = $this->faker->text();
        $election->requestLegal = $this->faker->text();
        $election->requestConfirmation = 'Merci <b>{{ email }}</b> !';

        $election->proxyTitle = '<span class="text-[#00AEEF]">
                Le 9 juin,
            </span>
            <br>
            Je vote deux fois';
        $election->proxyDescription = $this->faker->text();
        $election->proxyLegal = $this->faker->text();
        $election->proxyConfirmation = 'Merci <b>{{ email }}</b> !';

        return $election;
    }

    private function createRound(
        string $name,
        string $date,
        ?UuidInterface $uuid = null,
    ): Round {
        $round = new Round($uuid);
        $round->name = $name;
        $round->description = $this->faker->text();
        $round->date = new \DateTimeImmutable($date);

        return $round;
    }
}
