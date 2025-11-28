<?php

declare(strict_types=1);

namespace App\Mailer\Transport;

use App\Mailer\AbstractEmailTemplate;
use App\Mailer\Command\AsyncSendMessageCommand;
use App\Mailer\Command\SendMessageCommand;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerTransport implements TransportInterface
{
    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    public function sendTemplateEmail(AbstractEmailTemplate $email, bool $async = true): void
    {
        if ($async) {
            $this->bus->dispatch(new AsyncSendMessageCommand($email->getUuid()));
        } else {
            $this->bus->dispatch(new SendMessageCommand($email->getUuid()));
        }
    }
}
