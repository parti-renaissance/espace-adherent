<?php

namespace AppBundle\Deputy;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DeputyMessage as Message;

class DeputyMessageNotifier
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMessage(DeputyMessage $message): void
    {
        $chunks = array_chunk(
            $message->getRecipients(),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage($this->createMessage($message, $chunk));
        }
    }

    private function createMessage(DeputyMessage $message, array $recipients): Message
    {
        return Message::create($message, $recipients);
    }
}
