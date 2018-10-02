<?php

namespace AppBundle\CitizenProject;

use AppBundle\Mail\Campaign\CitizenProjectContactActorsMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class CitizenProjectContactActorsCommandHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(CitizenProjectContactActorsCommand $command): void
    {
        $author = $command->getSender();

        $this->mailPost->address(
            CitizenProjectContactActorsMail::class,
            CitizenProjectContactActorsMail::createRecipientsFrom(array_merge([$author], $command->getRecipients())),
            CitizenProjectContactActorsMail::createRecipientFromAdherent($author),
            CitizenProjectContactActorsMail::createTemplateVars($author, $command->getMessage()),
            CitizenProjectContactActorsMail::createSubject($command->getSubject()),
            CitizenProjectContactActorsMail::createSender($author)
        );
    }
}
