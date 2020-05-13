<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\RemoveApplicationRequestCandidateCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveApplicationRequestCandidateCommandHandler implements MessageHandlerInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(RemoveApplicationRequestCandidateCommand $command): void
    {
        $this->manager->deleteApplicationRequestCandidate($command->getEmail());
    }
}
