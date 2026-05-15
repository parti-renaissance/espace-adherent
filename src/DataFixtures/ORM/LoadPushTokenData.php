<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\PushToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

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

        $this->addReference('push-token-1', $pushToken1);
        $this->addReference('push-token-2', $pushToken2);
        $this->addReference('push-token-3', $pushToken3);
        $this->addReference('push-token-4', $pushToken4);
        $this->addReference('push-token-dead', $pushToken5);
        $this->addReference('push-token-with-history', $pushToken6);

        // One unique token per adherent for realistic targeting tests
        foreach ([
            'adherent-1', 'adherent-2', 'adherent-3', 'adherent-4', 'adherent-5',
            'adherent-6', 'adherent-7', 'adherent-8', 'adherent-9', 'adherent-10',
            'adherent-55', 'president-ad-1',
        ] as $ref) {
            $token = $this->createPushToken('token-'.$ref);
            $manager->persist($token);
            $this->addReference('push-token-'.$ref, $token);
        }

        $manager->flush();
    }

    public function createPushToken(string $identifier): PushToken
    {
        return new PushToken(Uuid::v4(), $identifier);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDeviceData::class,
        ];
    }
}
