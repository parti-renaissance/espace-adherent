<?php

declare(strict_types=1);

namespace App\AdherentMessage\Sender;

use App\AdherentMessage\TransactionalMessage\TransactionalMessageFactory;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailer\MailerService;

class TransactionalSender implements SenderInterface
{
    public function __construct(private readonly MailerService $transactionalMailer)
    {
    }

    public function supports(AdherentMessageInterface $message, bool $forTest): bool
    {
        return $message->isStatutory();
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): void
    {
        $this->doSend($message, $recipients);
    }

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool
    {
        $message = clone $message;
        $message->setSubject(\sprintf('[TEST] %s', $message->getSubject()));

        return $this->doSend($message, $recipients);
    }

    private function doSend(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->transactionalMailer->sendMessage(TransactionalMessageFactory::createFromAdherentMessage($message, $recipients));
    }
}
