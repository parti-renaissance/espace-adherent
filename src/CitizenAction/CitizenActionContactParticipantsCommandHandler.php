<?php

namespace AppBundle\CitizenAction;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenActionContactParticipantsMessage;

class CitizenActionContactParticipantsCommandHandler
{
    private $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(CitizenActionContactParticipantsCommand $command): void
    {
        $chunks = array_chunk($command->getRecipients(), MailerService::PAYLOAD_MAXSIZE);

        foreach ($chunks as $chunk) {
            $this->mailer->sendMessage(CitizenActionContactParticipantsMessage::create(
                $chunk,
                $command->getSender(),
                $command->getSubject(),
                $command->getMessage()
            ));
        }
    }
}
