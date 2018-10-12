<?php

namespace AppBundle\Event;

use AppBundle\Mail\Campaign\EventContactMembersMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class EventContactMembersCommandHandler
{
    private $mailPost;

    public function __construct(MailPostInterface $mailPost)
    {
        $this->mailPost = $mailPost;
    }

    public function handle(EventContactMembersCommand $command): void
    {
        $this->mailPost->address(
            EventContactMembersMail::class,
            EventContactMembersMail::createRecipientsFrom($command->getRecipients()),
            EventContactMembersMail::createReplyToFrom($command->getSender()),
            EventContactMembersMail::createTemplateVars($command->getSender(), $command->getMessage()),
            EventContactMembersMail::createSubject($command->getSubject())
        );
    }
}
