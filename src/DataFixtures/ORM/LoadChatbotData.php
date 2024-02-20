<?php

namespace App\DataFixtures\ORM;

use App\Chatbot\Enum\AssistantTypeEnum;
use App\Chatbot\Enum\ChatbotTypeEnum;
use App\Entity\Chatbot\Chatbot;
use App\Entity\OpenAI\Assistant as OpenAIAssistant;
use App\Entity\TelegramBot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadChatbotData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getChatbots() as $key => $data) {
            $chatbot = $this->createChatbot(
                $data['name'],
                $data['type'],
                $data['assistantType'],
                $data['telegramBot'] ?? null,
                $data['openAiAssistant'] ?? null
            );

            $manager->persist($chatbot);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadTelegramBotData::class,
            LoadOpenAIAssistantData::class,
        ];
    }

    private function createChatbot(
        string $code,
        ChatbotTypeEnum $type,
        AssistantTypeEnum $assistantType,
        ?TelegramBot $telegramBot = null,
        ?OpenAIAssistant $openAiAssistant = null
    ): Chatbot {
        $chatbot = new Chatbot();
        $chatbot->name = $code;
        $chatbot->type = $type;
        $chatbot->assistantType = $assistantType;
        $chatbot->telegramBot = $telegramBot;
        $chatbot->openAiAssistant = $openAiAssistant;

        return $chatbot;
    }

    private function getChatbots(): \Generator
    {
        yield 'chatbot-1' => [
            'name' => 'Chatbot #1',
            'type' => ChatbotTypeEnum::TELEGRAM,
            'assistantType' => AssistantTypeEnum::OPENAI,
            'telegramBot' => $this->getReference('telegram-bot-1'),
            'openAiAssistant' => $this->getReference('openai-assistant-1'),
        ];
    }
}
