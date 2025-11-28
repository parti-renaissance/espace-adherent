<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentDeleteCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentDeleteCommandHandler
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(AdherentDeleteCommand $command): void
    {
        $this->manager->deleteMember($command);
    }
}
