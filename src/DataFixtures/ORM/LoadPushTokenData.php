<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\PushToken;
use App\PushToken\PushTokenSourceEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPushTokenData extends Fixture implements DependentFixtureInterface
{
    public const PUSH_TOKEN_1_UUID = '5a650f97-2100-4800-913e-17d82f69b7a3';
    public const PUSH_TOKEN_2_UUID = 'aa429e50-f55a-4da7-b1e6-3c12a55e031a';
    public const PUSH_TOKEN_3_UUID = '611bd38d-3150-4843-ad6a-51732b308e36';
    public const PUSH_TOKEN_4_UUID = 'c523cdd0-c4ad-4692-aa77-619c95a36414';

    public function load(ObjectManager $manager)
    {
        $adherent77 = $this->getReference('adherent-7');

        $pushToken1 = $this->createPushTokenForAdherent(
            self::PUSH_TOKEN_1_UUID,
            $adherent77,
            'token-francis-jemarche-1',
            PushTokenSourceEnum::JE_MARCHE
        );

        $pushToken2 = $this->createPushTokenForAdherent(
            self::PUSH_TOKEN_2_UUID,
            $adherent77,
            'token-francis-jemarche-2',
            PushTokenSourceEnum::JE_MARCHE
        );

        $pushToken3 = $this->createPushTokenForDevice(
            self::PUSH_TOKEN_3_UUID,
            $this->getReference('device-1'),
            'token-device-1-jemarche',
            PushTokenSourceEnum::JE_MARCHE
        );

        $pushToken4 = $this->createPushTokenForDevice(
            self::PUSH_TOKEN_4_UUID,
            $this->getReference('device-2'),
            'token-device-2-jemarche',
            PushTokenSourceEnum::JE_MARCHE
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
        string $source
    ): PushToken {
        return PushToken::createForAdherent(
            Uuid::fromString($uuid),
            $adherent,
            $identifier,
            $source
        );
    }

    public function createPushTokenForDevice(
        string $uuid,
        Device $device,
        string $identifier,
        string $source
    ): PushToken {
        return PushToken::createForDevice(
            Uuid::fromString($uuid),
            $device,
            $identifier,
            $source
        );
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDeviceData::class,
        ];
    }
}
