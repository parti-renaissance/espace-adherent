<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\RemoveCoalitionMemberCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveCoalitionMemberCommandHandler implements MessageHandlerInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(RemoveCoalitionMemberCommand $command): void
    {
        $this->manager->deleteCoalitionMember($command->getEmail());
    }
}
