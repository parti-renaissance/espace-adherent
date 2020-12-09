<?php

namespace App\CitizenAction;

use App\Mailer\MailerService;
use App\Mailer\Message\CitizenActionContactParticipantsMessage;

class CitizenActionContactParticipantsCommandHandler
{
    private $mailer;

    public function __construct(MailerService $transactionalMailer)
    {
        $this->mailer = $transactionalMailer;
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
