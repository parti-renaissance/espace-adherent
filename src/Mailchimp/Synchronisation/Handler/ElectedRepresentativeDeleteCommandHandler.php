<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeDeleteCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ElectedRepresentativeDeleteCommandHandler
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
