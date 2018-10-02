<?php

namespace AppBundle\Committee;

use AppBundle\Mail\Campaign\CommitteeContactMembersMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class CommitteeContactMembersCommandHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(CommitteeContactMembersCommand $command): void
    {
        $author = $command->getSender();

        $this->mailPost->address(
            CommitteeContactMembersMail::class,
            CommitteeContactMembersMail::createRecipients(array_merge([$author], $command->getRecipients())),
            CommitteeContactMembersMail::createRecipientFromAdherent($author),
            CommitteeContactMembersMail::createTemplateVars($author, $command->getMessage()),
            CommitteeContactMembersMail::createSubject($command->getSubject()),
            CommitteeContactMembersMail::createSender($author)
        );
    }
}
