<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Chatbot\Chatbot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadChatbotData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createChatbot('conformite-eu', 'asst_123'));

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
}
