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

    public function handle(CommitteeContactMembersCommand $command)
    {
        $this->mailjet->sendMessage(CommitteeContactMembersMessage::create(
            $command->getRecipients(),
            $command->getSender(),
            $command->getMessage()
        ));
    }
}
