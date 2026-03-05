<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Chatbot\Chatbot;
use App\Entity\Chatbot\Thread;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadChatbotData extends Fixture implements DependentFixtureInterface
{
    public const THREAD_1_UUID = 'a046adbe-57d6-4115-91bf-e8e68ef1e0fa';
    public const THREAD_2_UUID = 'b157bfcf-68e7-5226-a2c0-f9f79f020fbb';

    public function load(ObjectManager $manager): void
    {
        $chatbot = $this->createChatbot('conformite-eu', 'asst_123');
        $manager->persist($chatbot);

        // Thread 1: owned by canary tester (president-ad-1), 25 messages for pagination testing
        $adherent1 = $this->getReference('president-ad-1', Adherent::class);
        $thread1 = new Thread($adherent1, 'Mon premier thread', Uuid::fromString(self::THREAD_1_UUID));
        $thread1->chatbot = $chatbot;

        $base = new \DateTimeImmutable('2026-03-01 10:00:00');
        for ($i = 1; $i <= 25; ++$i) {
            $date = $base->modify("+{$i} minutes");
            if (1 === $i % 2) {
                $thread1->addUserMessage('Question '.(int) (($i + 1) / 2), $date);
            } else {
                $thread1->addAssistantMessage('Réponse '.(int) ($i / 2), $date);
            }
        }

        $manager->persist($thread1);

        // Thread 2: owned by another adherent (adherent-5), 2 messages for security isolation testing
        $adherent2 = $this->getReference('adherent-5', Adherent::class);
        $thread2 = new Thread($adherent2, 'Thread autre utilisateur', Uuid::fromString(self::THREAD_2_UUID));
        $thread2->chatbot = $chatbot;
        $thread2->addUserMessage('Question privée', new \DateTimeImmutable('2026-03-01 11:00:00'));
        $thread2->addAssistantMessage('Réponse privée', new \DateTimeImmutable('2026-03-01 11:01:00'));

        $manager->persist($thread2);

        $manager->flush();
    }

    private function createChatbot(
        string $code,
        string $assistantId,
        bool $enabled = true,
    ): Chatbot {
        $chatbot = new Chatbot();
        $chatbot->code = $code;
        $chatbot->assistantId = $assistantId;
        $chatbot->enabled = $enabled;

        return $chatbot;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}
