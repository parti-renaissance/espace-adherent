<?php

namespace AppBundle\Event;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\EventContactMembersMessage;

class EventContactMembersCommandHandler
{
    private $mailjet;

    public function __construct(MailjetService $mailjet)
    {
        $this->mailjet = $mailjet;
    }

    public function handle(EventContactMembersCommand $command)
    {
        $this->mailjet->sendMessage(EventContactMembersMessage::create(
            $command->getRecipients(),
            $command->getSender(),
            $command->getMessage()
        ));
    }
}
