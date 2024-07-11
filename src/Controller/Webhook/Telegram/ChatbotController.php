<?php

namespace App\Controller\Webhook\Telegram;

use App\Chatbot\Telegram\ConversationManager;
use App\Controller\CanaryControllerTrait;
use App\Entity\Chatbot\Chatbot;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

#[Entity('chatbot', expr: 'repository.findOneEnabledBySecret(secret)')]
#[Route('/telegram/chatbot/{secret}', name: self::ROUTE_NAME, methods: ['POST'])]
class ChatbotController extends AbstractController
{
    use CanaryControllerTrait;

    public const ROUTE_NAME = 'app_webhook_telegram_chatbot';

    public function __construct(private readonly ConversationManager $conversationManager)
    {
    }

    public function __invoke(Request $request, Chatbot $chatbot): Response
    {
        $this->disableInProduction();

        if (!$content = $request->getContent()) {
            throw new BadRequestHttpException('No content in request');
        }

        if (!$data = BotApi::jsonValidate($content, true)) {
            throw new BadRequestHttpException('Request content is not a valid Telegram webhook');
        }

        $update = Update::fromResponse($data);

        if (($message = $update->getMessage()) && ($messageText = $message->getText())) {
            $telegramChatId = $message->getChat()->getId();

            $this->conversationManager->addMessage($chatbot, $telegramChatId, $messageText);
        }

        return new Response();
    }
}
