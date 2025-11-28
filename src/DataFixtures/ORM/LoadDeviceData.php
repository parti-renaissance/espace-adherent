<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Device;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDeviceData extends Fixture implements DependentFixtureInterface
{
    public const DEVICE_1_UUID = '64a85323-fade-4d05-9db0-e06825fc5e61';
    public const DEVICE_2_UUID = '2d5f91ce-547f-44e0-b5f2-037c7a5a99ec';

    public function load(ObjectManager $manager): void
    {
        $device1 = $this->createDevice(
            self::DEVICE_1_UUID,
            'device_1'
        );

        $this->setReference('device-1', $device1);

        $device2 = $this->createDevice(
            self::DEVICE_2_UUID,
            'device_2',
            '92002'
        );
        $device2->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_city_92002'));

        $this->setReference('device-2', $device2);

        $manager->persist($device1);
        $manager->persist($device2);

        $manager->flush();
    }

    public function createDevice(string $uuid, string $deviceUuid, ?string $postalCode = null): Device
    {
        $device = new Device(Uuid::fromString($uuid), $deviceUuid, $postalCode);

        $device->login();

        return $device;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}
