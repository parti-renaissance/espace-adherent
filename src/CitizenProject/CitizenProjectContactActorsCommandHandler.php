<?php

namespace AppBundle\CitizenProject;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectContactActorsMessage;

class CitizenProjectContactActorsCommandHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(CitizenProjectContactActorsCommand $command): void
    {
        $chunks = array_chunk(
            array_merge([$command->getSender()], $command->getRecipients()),
            MailerService::PAYLOAD_MAXSIZE
        );

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage(CitizenProjectContactActorsMessage::create(
                $chunk,
                $command->getSender(),
                $command->getMessage()
            ));
        }
    }
}
