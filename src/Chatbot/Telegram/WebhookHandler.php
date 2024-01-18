<?php

namespace App\Chatbot\Telegram;

use App\Controller\Webhook\Telegram\ChatbotController;
use App\Entity\Chatbot\Chatbot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WebhookHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Client $client
    ) {
    }

    public function deleteWebhook(string $botApiToken): void
    {
        $this->client->deleteWebhook($botApiToken);
    }

    public function handleChanges(Chatbot $chatbot, string $botApiTokenBeforeUpdate = null): void
    {
        $botApiTokenAfterUpdate = $chatbot->telegramBotApiToken;

        if ($botApiTokenAfterUpdate === $botApiTokenBeforeUpdate) {
            return;
        }

        if (!$botApiTokenAfterUpdate) {
            if ($botApiTokenBeforeUpdate) {
                $this->deleteWebhook($botApiTokenBeforeUpdate);
            }

            return;
        }

        $chatbot->generateTelegramBotSecret();

        $this->entityManager->flush();

        $webhookUrl = $this->generateWebhookUrl($chatbot->telegramBotSecret);

        $this->client->setWebhook($botApiTokenAfterUpdate, $webhookUrl);
    }

    private function generateWebhookUrl(string $secret): string
    {
        return sprintf(
            '%s/telegram/chatbot/%s',
            'https://f367-2a01-cb00-d26-5600-2983-c041-b6f5-b46.ngrok-free.app',
            $secret
        );

        return $this->urlGenerator->generate(ChatbotController::ROUTE_NAME, [
            'secret' => $secret,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
