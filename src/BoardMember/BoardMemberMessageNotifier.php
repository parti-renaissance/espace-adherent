<?php

namespace App\BoardMember;

use App\Mailer\MailerService;
use App\Mailer\Message\BoardMemberContactAdherentsMessage;

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

    private function createMessage(BoardMemberMessage $message, array $recipients): BoardMemberContactAdherentsMessage
    {
        return BoardMemberContactAdherentsMessage::createFromModel($message, $recipients);
    }
}
