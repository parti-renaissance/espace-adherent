<?php

namespace AppBundle\Committee;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeContactMembersMessage;

class CommitteeContactMembersCommandHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(CommitteeContactMembersCommand $command): void
    {
        $chunks = array_chunk($command->getRecipients(), MailjetService::PAYLOAD_MAXSIZE);

        foreach ($chunks as $chunk) {
            $this->mailjet->sendMessage(CommitteeContactMembersMessage::create(
                $chunk,
                $command->getSender(),
                $command->getMessage()
            ));
        }
    }
}
