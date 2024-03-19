<?php

namespace App\DataFixtures\ORM;

use App\Entity\ProcurationV2\ProcurationRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LoadProcurationV2ProcurationRequestData extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createProcurationRequest('john.durand@test.dev'));
        $manager->persist($this->createProcurationRequest('jane.martin@test.dev'));
        $manager->persist($this->createProcurationRequest('jack.doe@test.dev'));
        $manager->persist($this->createProcurationRequest('pascal.dae@test.dev'));

        $manager->flush();
    }

    private function createProcurationRequest(string $email): ProcurationRequest
    {
        $procurationRequest = new ProcurationRequest();
        $procurationRequest->email = $email;
        $procurationRequest->clientIp = $this->faker->ipv4();

        return $procurationRequest;
    }
}
