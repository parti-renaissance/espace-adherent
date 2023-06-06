<?php

namespace App\Mailer\Transport;

use App\Mailer\AbstractEmailTemplate;
use App\Mailer\Command\SendMessageCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerTransport implements TransportInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function sendTemplateEmail(AbstractEmailTemplate $email): void
    {
        $this->bus->dispatch(new SendMessageCommand($email->getUuid()));
    }
}
