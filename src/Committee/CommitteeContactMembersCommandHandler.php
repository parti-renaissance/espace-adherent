<?php

namespace App\Committee;

use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeContactMembersMessage;

class CommitteeContactMembersCommandHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(CommitteeContactMembersCommand $command): void
    {
        $chunks = array_chunk(
            array_merge([$command->getSender()], $command->getRecipients()),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage(CommitteeContactMembersMessage::create(
                $chunk,
                $command->getSender(),
                $command->getSubject(),
                $command->getMessage()
            ));
        }
    }
}
