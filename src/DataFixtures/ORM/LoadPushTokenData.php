<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\PushToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPushTokenData extends Fixture implements DependentFixtureInterface
{
    public const PUSH_TOKEN_1_UUID = '5a650f97-2100-4800-913e-17d82f69b7a3';
    public const PUSH_TOKEN_2_UUID = 'aa429e50-f55a-4da7-b1e6-3c12a55e031a';
    public const PUSH_TOKEN_3_UUID = '611bd38d-3150-4843-ad6a-51732b308e36';
    public const PUSH_TOKEN_4_UUID = 'c523cdd0-c4ad-4692-aa77-619c95a36414';

    public function load(ObjectManager $manager): void
    {
        $adherent77 = $this->getReference('adherent-7', Adherent::class);

        $pushToken1 = $this->createPushTokenForAdherent(
            self::PUSH_TOKEN_1_UUID,
            $adherent77,
            'token-francis-jemarche-1',
        );

        $pushToken2 = $this->createPushTokenForAdherent(
            self::PUSH_TOKEN_2_UUID,
            $adherent77,
            'token-francis-jemarche-2',
        );

        $pushToken3 = $this->createPushTokenForDevice(
            self::PUSH_TOKEN_3_UUID,
            $this->getReference('device-1', Device::class),
            'token-device-1-jemarche',
        );

        $pushToken4 = $this->createPushTokenForDevice(
            self::PUSH_TOKEN_4_UUID,
            $this->getReference('device-2', Device::class),
            'token-device-2-jemarche',
        );

        $manager->persist($pushToken1);
        $manager->persist($pushToken2);
        $manager->persist($pushToken3);
        $manager->persist($pushToken4);

        $manager->flush();
    }

    public function createPushTokenForAdherent(
        string $uuid,
        Adherent $adherent,
        string $identifier,
    ): PushToken {
        return PushToken::createForAdherent(
            Uuid::fromString($uuid),
            $adherent,
            $identifier,
        );
    }

    public function createPushTokenForDevice(
        string $uuid,
        Device $device,
        string $identifier,
    ): PushToken {
        return PushToken::createForDevice(
            Uuid::fromString($uuid),
            $device,
            $identifier,
        );
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDeviceData::class,
        ];
    }
}
