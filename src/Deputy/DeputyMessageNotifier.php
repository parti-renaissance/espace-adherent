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

    public function sendMessage(DeputyMessage $message, array $recipients): void
    {
        if (empty($recipients)) {
            return;
        }

        array_unshift(
            $recipients,
            new DeputyRecipient('territoires@en-marche.fr', 'Pôle Territoire')
        );

        $chunks = array_chunk(
            $recipients,
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage($this->createMessage($message, $chunk), false);
        }
    }

    private function createMessage(DeputyMessage $message, array $recipients): Message
    {
        return Message::create($message, $recipients);
    }
}
