<?php

namespace App\Chatbot\EventListener;

use App\Chatbot\Event\UserMessageEvent;
use App\Chatbot\ThreadFactory;
use App\Entity\TelegramBot;
use App\Repository\Chatbot\ChatbotRepository;
use App\Repository\Chatbot\ThreadRepository;
use App\Telegram\Event\UserMessageEvent as TelegramUserMessageEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEventListener]
class TelegramUserMessageListener
{
    public function __construct(
        private readonly ChatbotRepository $chatbotRepository,
        private readonly ThreadRepository $threadRepository,
        private readonly ThreadFactory $threadFactory,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public function __invoke(TelegramUserMessageEvent $event): void
    {
        $telegramBot = $event->bot;
        $telegramMessage = $event->message;

        if (!$telegramBot instanceof TelegramBot) {
            return;
        }

        $chatbot = $this->chatbotRepository->findOneByTelegramBot($telegramBot);

        if (!$chatbot || !$chatbot->isTelegramBot()) {
            return;
        }

        $telegramChatId = $telegramMessage->chatId;

        $thread = $this->threadRepository->findOneForTelegram($chatbot, $telegramChatId);

        if (!$thread) {
            $thread = $this->threadFactory->createTelegramThread($chatbot, $telegramChatId);
        }

        $message = $this->threadFactory->createUserMessage(
            $thread,
            $telegramMessage->text,
            $telegramMessage->entities,
            $telegramMessage->date
        );

        $thread->messages->add($message);

        $this->threadRepository->save($thread);

        $this->dispatcher->dispatch(new UserMessageEvent($message));
    }
}
