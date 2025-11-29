<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadAdherentRequestData extends Fixture implements DependentFixtureInterface
{
    public const ADHERENT_REQUEST_1_UUID = 'b03cee7b-6ace-4acd-96ff-a3f1037cf9f7';
    public const ADHERENT_REQUEST_2_UUID = '3edb2e0a-f0d7-4fb5-aa75-b8b965beb3cb';
    public const ADHERENT_REQUEST_3_UUID = '37aa3e2a-0928-41d0-a6f1-af06c3facac1';
    public const ADHERENT_REQUEST_4_UUID = '20ee41c3-f81b-4a7d-ad5e-5c2c3789b2b1';

    public function load(ObjectManager $manager): void
    {
        $adherentRequest1 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_1_UUID,
            'renaissance-user-1@en-marche-dev.fr',
        );

        $adherentRequest2 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_2_UUID,
            'future-renaissance-user-2@en-marche-dev.fr',
        );

        $adherentRequest3 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_3_UUID,
            'future-renaissance-user-3@en-marche-dev.fr',
        );

        $adherentRequest4 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_4_UUID,
            'michelle.dufour@example.ch',
            $this->getReference('adherent-1', Adherent::class)
        );

        $manager->persist($adherentRequest1);
        $manager->persist($adherentRequest2);
        $manager->persist($adherentRequest3);
        $manager->persist($adherentRequest4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function createAdherentRequest(
        string $uuid,
        string $email,
        ?Adherent $adherent = null,
    ): AdherentRequest {
        $adherentRequest = new AdherentRequest(Uuid::fromString($uuid));
        $adherentRequest->email = $email;
        $adherentRequest->emailHash = Adherent::createUuid($email);

        if ($adherent) {
            $adherentRequest->email = null;
            $adherentRequest->adherent = $adherent;
        }

        return $adherentRequest;
    }
}
