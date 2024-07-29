<?php

namespace App\AdherentMessage\Sender;

use App\AdherentMessage\TransactionalMessage\TransactionalMessageFactory;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\TransactionalMessageInterface;
use App\Mailer\MailerService;

class TransactionalSender implements SenderInterface
{
    private $mailer;

    public function __construct(MailerService $transactionalMailer)
    {
        $this->mailer = $transactionalMailer;
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof TransactionalMessageInterface;
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->doSend($message, $recipients);
    }

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool
    {
        $message = clone $message;
        $message->setSubject(\sprintf('[TEST] %s', $message->getSubject()));

        return $this->doSend($message, $recipients);
    }

    public function renderMessage(AdherentMessageInterface $message, array $recipients = []): string
    {
        return $this->mailer->renderMessage(TransactionalMessageFactory::createFromAdherentMessage($message, $recipients));
    }

    private function doSend(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->mailer->sendMessage(TransactionalMessageFactory::createFromAdherentMessage($message, $recipients));
    }
}
