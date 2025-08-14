<?php

namespace App\AdherentMessage\Sender;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\JeMengage\Push\Command\AdherentMessageSentNotificationCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class PushSender implements SenderInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function supports(AdherentMessageInterface $message, bool $forTest): bool
    {
        return AdherentMessageInterface::SOURCE_VOX === $message->getSource() && false === $forTest;
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): void
    {
        $this->bus->dispatch(new AdherentMessageSentNotificationCommand($message->getUuid()));
    }

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool
    {
        throw new \LogicException('PushSender does not support test sending.');
    }
}
