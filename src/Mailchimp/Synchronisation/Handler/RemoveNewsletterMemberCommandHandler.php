<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\RemoveNewsletterMemberCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveNewsletterMemberCommandHandler implements MessageHandlerInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(RemoveNewsletterMemberCommand $command): void
    {
        $this->manager->deleteNewsletterMember($command->getEmail());
    }
}
