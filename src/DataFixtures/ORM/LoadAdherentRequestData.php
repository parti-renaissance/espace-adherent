<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\FranceCities\FranceCities;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class LoadAdherentRequestData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public const ADHERENT_REQUEST_1_UUID = 'b03cee7b-6ace-4acd-96ff-a3f1037cf9f7';
    public const ADHERENT_REQUEST_2_UUID = '3edb2e0a-f0d7-4fb5-aa75-b8b965beb3cb';
    public const ADHERENT_REQUEST_3_UUID = '37aa3e2a-0928-41d0-a6f1-af06c3facac1';

    private PasswordEncoderInterface $encoder;

    public function __construct(FranceCities $franceCities, EncoderFactoryInterface $encoders)
    {
        parent::__construct($franceCities);

        $this->encoder = $encoders->getEncoder(Adherent::class);
    }

    public function load(ObjectManager $manager)
    {
        $adherentRequest1 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_1_UUID,
            'renaissance-user-1@en-marche-dev.fr',
            'Laure',
            'Fenix',
            30.25,
            $this->createPostAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923),
            true,
            true
        );

        $adherentRequest2 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_2_UUID,
            'future-renaissance-user-2@en-marche-dev.fr',
            'Daniel',
            'Dumas',
            10.50,
            $this->createPostAddress('44 rue des courcelles', '75008-75108'),
            true,
        );

        $adherentRequest3 = $this->createAdherentRequest(
            self::ADHERENT_REQUEST_3_UUID,
            'future-renaissance-user-3@en-marche-dev.fr',
            'Amelie',
            'Moulin',
            30.75,
            $this->createPostAddress('2 avenue Jean Jaurès', '77000-77288', null, 48.5278939, 2.6484923)
        );

        $manager->persist($adherentRequest1);
        $manager->persist($adherentRequest2);
        $manager->persist($adherentRequest3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function createAdherentRequest(
        string $uuid,
        string $email,
        string $firstName,
        string $lastName,
        int $amount,
        PostAddress $address,
        $allowEmailNotifications = false,
        $allowMobileNotifications = false,
    ): AdherentRequest {
        $adherentRequest = new AdherentRequest(Uuid::fromString($uuid));
        $adherentRequest->firstName = $firstName;
        $adherentRequest->lastName = $lastName;
        $adherentRequest->email = $email;
        $adherentRequest->amount = $amount;
        $adherentRequest->setPostAddress($address);
        $adherentRequest->password = $this->encoder->encodePassword(LoadAdherentData::DEFAULT_PASSWORD, null);
        $adherentRequest->allowEmailNotifications = $allowEmailNotifications;
        $adherentRequest->allowMobileNotifications = $allowMobileNotifications;

        return $adherentRequest;
    }
}
