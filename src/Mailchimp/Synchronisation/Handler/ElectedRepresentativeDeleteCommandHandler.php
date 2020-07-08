<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeDeleteCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ElectedRepresentativeDeleteCommandHandler implements MessageHandlerInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(ElectedRepresentativeDeleteCommand $command): void
    {
        $this->manager->deleteElectedRepresentative($command->getEmail());
    }
}
