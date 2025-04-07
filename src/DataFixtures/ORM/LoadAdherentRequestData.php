<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\FranceCities\FranceCities;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LoadAdherentRequestData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const ADHERENT_REQUEST_1_UUID = 'b03cee7b-6ace-4acd-96ff-a3f1037cf9f7';
    public const ADHERENT_REQUEST_2_UUID = '3edb2e0a-f0d7-4fb5-aa75-b8b965beb3cb';
    public const ADHERENT_REQUEST_3_UUID = '37aa3e2a-0928-41d0-a6f1-af06c3facac1';
    public const ADHERENT_REQUEST_4_UUID = '20ee41c3-f81b-4a7d-ad5e-5c2c3789b2b1';

    private PasswordHasherInterface $hasher;

    public function __construct(FranceCities $franceCities, PasswordHasherFactoryInterface $hasherFactory)
    {
        parent::__construct($franceCities);

        $this->hasher = $hasherFactory->getPasswordHasher(Adherent::class);
    }

    public function load(ObjectManager $manager): void
    {
        $adherentRequest1 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_1_UUID,
            'renaissance-user-1@en-marche-dev.fr',
            30.25,
            true,
            true
        );

        $adherentRequest2 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_2_UUID,
            'future-renaissance-user-2@en-marche-dev.fr',
            10.50,
            true,
        );

        $adherentRequest3 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_3_UUID,
            'future-renaissance-user-3@en-marche-dev.fr',
            30.75,
        );

        $adherentRequest4 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_4_UUID,
            'michelle.dufour@example.ch',
            20,
            true,
            true,
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
        int $amount,
        $allowEmailNotifications = false,
        $allowMobileNotifications = false,
        ?Adherent $adherent = null,
    ): AdherentRequest {
        $adherentRequest = new AdherentRequest(Uuid::fromString($uuid));
        $adherentRequest->email = $email;
        $adherentRequest->amount = $amount;
        $adherentRequest->allowEmailNotifications = $allowEmailNotifications;
        $adherentRequest->allowMobileNotifications = $allowMobileNotifications;

        if ($adherent) {
            $adherentRequest->email = null;
            $adherentRequest->adherentUuid = Adherent::createUuid($adherent->getEmailAddress());
            $adherentRequest->adherent = $adherent;
        }

        return $adherentRequest;
    }
}
