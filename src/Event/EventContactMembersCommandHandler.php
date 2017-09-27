<?php

namespace AppBundle\Event;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\EventContactMembersMessage;

class EventContactMembersCommandHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(EventContactMembersCommand $command): void
    {
        $chunks = array_chunk($command->getRecipients(), MailerService::PAYLOAD_MAXSIZE);

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage(EventContactMembersMessage::create(
                $chunk,
                $command->getSender(),
                $command->getMessage()
            ));
        }
    }
}
