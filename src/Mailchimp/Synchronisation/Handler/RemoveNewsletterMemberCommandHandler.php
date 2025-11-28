<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\RemoveNewsletterMemberCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RemoveNewsletterMemberCommandHandler
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
