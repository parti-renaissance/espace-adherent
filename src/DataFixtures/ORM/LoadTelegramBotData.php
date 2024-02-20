<?php

namespace App\DataFixtures\ORM;

use App\Entity\TelegramBot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadTelegramBotData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getTelegramBots() as $key => $data) {
            $telegramBot = $this->createTelegramBot(
                $data['name'],
                $data['apiToken'],
                $data['secret'],
                $data['blacklistedIds'] ?? [],
                $data['whitelistedIds'] ?? [],
                $data['enabled'] ?? true
            );

            $manager->persist($telegramBot);

            $this->setReference($key, $telegramBot);
        }

        $manager->flush();
    }

    private function createTelegramBot(
        string $name,
        string $apiToken,
        string $secret,
        array $blacklistedIds = [],
        array $whitelistedIds = [],
        bool $enabled = true
    ): TelegramBot {
        $telegramBot = new TelegramBot();
        $telegramBot->name = $name;
        $telegramBot->apiToken = $apiToken;
        $telegramBot->secret = $secret;
        $telegramBot->blacklistedIds = $blacklistedIds;
        $telegramBot->whitelistedIds = $whitelistedIds;
        $telegramBot->enabled = $enabled;

        return $telegramBot;
    }

    private function getTelegramBots(): \Generator
    {
        yield 'telegram-bot-1' => [
            'name' => 'Bot #1',
            'apiToken' => 'bot-1-api-token',
            'secret' => 'bot-1-secret',
        ];

        yield 'telegram-bot-2' => [
            'name' => 'Bot #2 (with blacklist)',
            'apiToken' => 'bot-2-api-token',
            'secret' => 'bot-2-secret',
            'blacklistedIds' => ['blacklisted_id_1', 'blacklisted_id_2'],
        ];

        yield 'telegram-bot-3' => [
            'name' => 'Bot #3 (with whitelist)',
            'apiToken' => 'bot-3-api-token',
            'secret' => 'bot-3-secret',
            'whitelistedIds' => ['whitelisted_id_1', 'whitelisted_id_2'],
        ];

        yield 'telegram-bot-4' => [
            'name' => 'Bot #4 (with blacklist and whitelist)',
            'apiToken' => 'bot-4-api-token',
            'secret' => 'bot-4-secret',
            'blacklistedIds' => ['blacklisted_id_1', 'blacklisted_id_2'],
            'whitelistedIds' => ['whitelisted_id_1', 'whitelisted_id_2'],
        ];

        yield 'telegram-bot-5' => [
            'name' => 'Bot #5 (disabled)',
            'apiToken' => 'bot-5-api-token',
            'secret' => 'bot-5-secret',
            'enabled' => false,
        ];
    }
}
