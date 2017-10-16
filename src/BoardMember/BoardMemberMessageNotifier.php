<?php

namespace AppBundle\BoardMember;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\BoardMemberMessage as MailjetMessage;

class BoardMemberMessageNotifier
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function sendMessage(BoardMemberMessage $message): void
    {
        $chunks = array_chunk(
            $message->getRecipients(),
            MailjetService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailjet->sendMessage($this->createMessage($message, $chunk));
        }
    }

    private function createMessage(BoardMemberMessage $message, array $recipients): MailjetMessage
    {
        return MailjetMessage::createFromModel($message, $recipients);
    }
}
