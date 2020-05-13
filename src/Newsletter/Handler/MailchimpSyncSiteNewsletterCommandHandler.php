<?php

namespace App\Newsletter\Handler;

use App\Mailchimp\Manager;
use App\Newsletter\Command\MailchimpSyncSiteNewsletterCommand;
use App\Newsletter\NewsletterValueObject;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class MailchimpSyncSiteNewsletterCommandHandler implements MessageHandlerInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(MailchimpSyncSiteNewsletterCommand $command): void
    {
        $this->manager->editNewsletterMember(
            NewsletterValueObject::createFromSiteNewsletterCommand($command)
        );
    }
}
