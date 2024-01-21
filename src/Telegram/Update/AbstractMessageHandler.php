<?php

namespace App\Telegram\Update;

use App\Telegram\BotInterface;
use App\Telegram\Enum\ChatTypeEnum;
use TelegramBot\Api\Types\Chat;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

abstract class AbstractMessageHandler implements UpdateHandlerInterface
{
    private const HANDLED_CHAT_TYPES = [
        ChatTypeEnum::PRIVATE,
        ChatTypeEnum::GROUP,
    ];

    public function supports(BotInterface $bot, Update $update): bool
    {
        return ($message = $update->getMessage())
            && $this->isHandledChatType($message->getChat())
            && $this->isMessageGranted($bot, $message)
            && $this->isHandledMessage($message);
    }

    abstract protected function isHandledMessage(Message $message): bool;

    private function isHandledChatType(Chat $chat): bool
    {
        return \in_array($chat->getType(), self::HANDLED_CHAT_TYPES, true);
    }

    private function isMessageGranted(BotInterface $bot, Message $message): bool
    {
        $sender = $message->getFrom();

        if (!$sender || $sender->isBot()) {
            return false;
        }

        $senderId = $sender->getId();
        $chatId = $message->getChat()->getId();

        if ($this->isBlacklisted($bot, $senderId)) {
            return false;
        }

        if ($chatId === $senderId) {
            // private chat
            return $this->isWhitelisted($bot, $sender->getId());
        }

        // group chat
        return !$this->isBlacklisted($bot, $chatId)
            && $this->isWhitelisted($bot, $chatId);
    }

    private function isBlacklisted(BotInterface $bot, string $chatId): bool
    {
        return \in_array($chatId, $bot->getBlacklistedChatIds(), true);
    }

    private function isWhitelisted(BotInterface $bot, string $chatId): bool
    {
        return empty($whitelistedChatIds = $bot->getWhitelistedChatIds())
            || \in_array($chatId, $whitelistedChatIds, true);
    }
}
