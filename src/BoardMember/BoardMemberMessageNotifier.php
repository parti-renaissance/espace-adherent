<?php

namespace AppBundle\BoardMember;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\BoardMemberMessage as Message;

class BoardMemberMessageNotifier
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMessage(BoardMemberMessage $message): void
    {
        $chunks = array_chunk(
            $message->getRecipients(),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage($this->createMessage($message, $chunk));
        }
    }

    private function createMessage(BoardMemberMessage $message, array $recipients): Message
    {
        return Message::createFromModel($message, $recipients);
    }
}
