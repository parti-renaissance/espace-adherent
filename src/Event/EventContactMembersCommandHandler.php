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

    public function handle(EventContactMembersCommand $command): void
    {
        $chunks = array_chunk($command->getRecipients(), 100);
        foreach ($chunks as $chunk) {
            $this->mailjet->sendMessage(EventContactMembersMessage::create(
                $chunk,
                $command->getSender(),
                $command->getMessage()
            ));
        }
    }
}
