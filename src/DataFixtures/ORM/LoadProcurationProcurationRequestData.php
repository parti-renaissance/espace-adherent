<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Procuration\ProcurationRequest;
use App\Procuration\InitialRequestTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadProcurationProcurationRequestData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createProcurationRequest('john.durand@test.dev', InitialRequestTypeEnum::PROXY));
        $manager->persist($this->createProcurationRequest('jane.martin@test.dev', InitialRequestTypeEnum::PROXY));
        $manager->persist($this->createProcurationRequest('jack.doe@test.dev', InitialRequestTypeEnum::REQUEST));
        $manager->persist($this->createProcurationRequest('pascal.dae@test.dev', InitialRequestTypeEnum::REQUEST));

        $manager->flush();
    }

    private function createProcurationRequest(string $email, InitialRequestTypeEnum $type): ProcurationRequest
    {
        $procurationRequest = new ProcurationRequest();
        $procurationRequest->email = $email;
        $procurationRequest->type = $type;
        $procurationRequest->clientIp = $this->faker->ipv4();

        return $procurationRequest;
    }
}
