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
    public function load(ObjectManager $manager): void
    {
        foreach ($manager->getRepository(Adherent::class)->findAll() as $adherent) {
            $manager->persist($this->createPushTokenForAdherent($adherent, bin2hex(random_bytes(16))));
        }

        $adherent77 = $this->getReference('adherent-7', Adherent::class);

        $pushToken1 = $this->createPushTokenForAdherent(
            $adherent77,
            'token-francis-jemarche-1',
        );

        $pushToken2 = $this->createPushTokenForAdherent(
            $adherent77,
            'token-francis-jemarche-2',
        );

        $pushToken3 = $this->createPushTokenForDevice(
            $this->getReference('device-1', Device::class),
            'token-device-1-jemarche',
        );

        $pushToken4 = $this->createPushTokenForDevice(
            $this->getReference('device-2', Device::class),
            'token-device-2-jemarche',
        );

        $manager->persist($pushToken1);
        $manager->persist($pushToken2);
        $manager->persist($pushToken3);
        $manager->persist($pushToken4);

        $manager->flush();
    }

    public function createPushTokenForAdherent(Adherent $adherent, string $identifier): PushToken
    {
        return PushToken::createForAdherent(Uuid::uuid4(), $adherent, $identifier);
    }

    public function createPushTokenForDevice(Device $device, string $identifier): PushToken
    {
        return PushToken::createForDevice(Uuid::uuid4(), $device, $identifier);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDeviceData::class,
        ];
    }
}
