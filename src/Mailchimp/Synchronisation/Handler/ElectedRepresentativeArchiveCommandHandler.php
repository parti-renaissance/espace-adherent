<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeArchiveCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ElectedRepresentativeArchiveCommandHandler
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(ElectedRepresentativeArchiveCommand $command): void
    {
        $this->manager->archiveElectedRepresentative($command->getEmail());
    }
}
