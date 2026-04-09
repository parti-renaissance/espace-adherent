<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AppSession;
use App\Entity\Notification;
use App\Entity\OAuth\Client;
use App\Entity\PushNotification;
use App\Entity\PushToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPushNotificationData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $client = $this->getReference('client-vox', Client::class);

        $tokenA1 = new PushToken(Uuid::uuid4(), 'token-pn-adherent-1');
        $tokenA2 = new PushToken(Uuid::uuid4(), 'token-pn-adherent-2');
        $tokenA3 = new PushToken(Uuid::uuid4(), 'token-pn-adherent-3');
        $tokenA4 = new PushToken(Uuid::uuid4(), 'token-pn-adherent-4');

        $manager->persist($tokenA1);
        $manager->persist($tokenA2);
        $manager->persist($tokenA3);
        $manager->persist($tokenA4);

        $pairs = [
            ['adherent-1', $tokenA1],
            ['adherent-2', $tokenA2],
            ['adherent-3', $tokenA3],
            ['adherent-4', $tokenA4],
        ];

        foreach ($pairs as [$ref, $token]) {
            $session = new AppSession($this->getReference($ref, Adherent::class), $client);
            $session->appVersion = 'v6.0.0';
            $session->addPushToken($token);
            $manager->persist($session);
        }

        $manager->flush();

        $this->createNotification(
            $manager,
            'NewArticleFixture',
            'Nouvel article publié',
            'Découvrez notre dernier article sur la réforme.',
            'national',
            ['article_id' => 42],
            [$tokenA1->identifier, $tokenA2->identifier, $tokenA3->identifier, $tokenA4->identifier],
        );

        $this->createNotification(
            $manager,
            'EventReminderFixture',
            'Rappel : événement ce soir',
            'N\'oubliez pas l\'événement militant à 19h.',
            'national',
            ['event_id' => 7],
            [$tokenA1->identifier, $tokenA2->identifier],
        );

        $manager->flush();
    }

    private function createNotification(
        ObjectManager $manager,
        string $className,
        string $title,
        string $body,
        string $scope,
        array $data,
        array $tokens,
    ): void {
        $pushNotification = new PushNotification($className, $title, $body, $scope, $data, 1);
        $pushNotification->recordChunkResult(\count($tokens), \count($tokens), 0);

        $chunk = new Notification($className, $title, $body, $data, $scope, null, $tokens);
        $chunk->pushNotification = $pushNotification;
        $chunk->setDelivered();

        $manager->persist($pushNotification);
        $manager->persist($chunk);
    }

    public function getDependencies(): array
    {
        return [
            LoadAppSessionData::class,
        ];
    }
}
