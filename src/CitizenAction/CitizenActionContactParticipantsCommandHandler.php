<?php

namespace AppBundle\CitizenAction;

use AppBundle\Mail\Campaign\CitizenActionContactParticipantsMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class CitizenActionContactParticipantsCommandHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(CitizenActionContactParticipantsCommand $command): void
    {
        $this->mailPost->address(
            CitizenActionContactParticipantsMail::class,
            CitizenActionContactParticipantsMail::createRecipientsFrom($command->getRecipients()),
            CitizenActionContactParticipantsMail::createRecipientFromAdherent($command->getSender()),
            CitizenActionContactParticipantsMail::createTemplateVarsFrom($command),
            '[Action citoyenne] '.$command->getSubject(),
            CitizenActionContactParticipantsMail::createSenderFrom($command->getSender())
        );
    }
}
