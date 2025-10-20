<?php

namespace App\DataFixtures\ORM;

use App\Entity\PushToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPushTokenData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $pushToken1 = $this->createPushToken('token-francis-jemarche-1');
        $pushToken2 = $this->createPushToken('token-francis-jemarche-2');
        $pushToken3 = $this->createPushToken('token-device-1-jemarche');
        $pushToken4 = $this->createPushToken('token-device-2-jemarche');

        $manager->persist($pushToken1);
        $manager->persist($pushToken2);
        $manager->persist($pushToken3);
        $manager->persist($pushToken4);

        $manager->flush();
    }

    public function createPushToken(string $identifier): PushToken
    {
        return new PushToken(Uuid::uuid4(), $identifier);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDeviceData::class,
        ];
    }
}
