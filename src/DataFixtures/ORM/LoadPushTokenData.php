<?php

declare(strict_types=1);

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

        $pushToken5 = $this->createPushToken('token-dead-device');
        $pushToken5->unsubscribedAt = new \DateTime('-1 day');

        $pushToken6 = $this->createPushToken('token-with-history');
        $pushToken6->lastNotificationAt = new \DateTime('-1 hour');
        $pushToken6->lastNotificationSuccess = true;

        $manager->persist($pushToken1);
        $manager->persist($pushToken2);
        $manager->persist($pushToken3);
        $manager->persist($pushToken4);
        $manager->persist($pushToken5);
        $manager->persist($pushToken6);

        $this->addReference('push-token-dead', $pushToken5);
        $this->addReference('push-token-with-history', $pushToken6);

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
