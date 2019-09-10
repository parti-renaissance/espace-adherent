<?php

namespace AppBundle\Newsletter\Handler;

use AppBundle\Mailchimp\Manager;
use AppBundle\Newsletter\Command\MailchimpSyncSiteNewsletterCommand;
use AppBundle\Newsletter\NewsletterValueObject;
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
