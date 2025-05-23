<?php

namespace App\DataFixtures\ORM;

use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use App\Entity\Adherent;
use App\Entity\AppSession;
use App\Entity\OAuth\Client;
use App\Entity\PushToken;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadAppSessionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $clientVox = $this->getReference('client-vox', Client::class);
        $clientCadre = $this->getReference('client-cadre', Client::class);

        $faker = Factory::create('fr_FR');
        $pushTokenRepository = $manager->getRepository(PushToken::class);

        foreach ([
            'adherent-1',
            'adherent-2',
            'adherent-3',
            'adherent-4',
            'adherent-5',
            'adherent-6',
            'adherent-7',
            'adherent-8',
            'adherent-9',
            'adherent-10',
            'adherent-55',
            'president-ad-1',
        ] as $i => $ref) {
            $user = $this->getReference($ref, Adherent::class);
            $pushTokens = $pushTokenRepository->findBy(['adherent' => $user]);

            $client = $clientVox;
            $appSystem = SystemEnum::all()[random_int(0, 2)];

            if (0 === $i % 3) {
                $appSystem = SystemEnum::WEB;
                $client = $clientCadre;
            }

            $manager->persist($session = new AppSession($user, $client));
            $session->appSystem = $appSystem;
            $session->appVersion = 'v5.15.0#10';
            $session->userAgent = $faker->userAgent();
            $session->ip = $faker->ipv4();

            if ($pushTokens) {
                $session->addPushToken($pushTokens[0]);
            }

            $nbPast = random_int(0, 3);
            for ($j = 0; $j < $nbPast; ++$j) {
                $manager->persist($pastSession = new AppSession($user, $client));
                $pastSession->appSystem = $appSystem;
                $pastSession->status = SessionStatusEnum::TERMINATED;
                $pastSession->appVersion = 'v5.15.0#10';
                $pastSession->userAgent = $faker->userAgent();
                $pastSession->ip = $faker->ipv6();
                if ($pushTokens) {
                    $pastSession->addPushToken($pushTokens[0]);
                }
                if (0 === $j % 2) {
                    $pastSession->unsubscribe();
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadClientData::class,
            LoadPushTokenData::class,
        ];
    }
}
