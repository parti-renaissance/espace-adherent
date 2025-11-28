<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Mailchimp\Manager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentMessageDeleteCommandHandler
{
    private $mailchimpManager;

    public function __construct(Manager $mailchimpManager)
    {
        $this->mailchimpManager = $mailchimpManager;
    }

    public function __invoke(AdherentMessageDeleteCommand $command): void
    {
        $this->mailchimpManager->deleteCampaign($command->getCampaignId());
    }
}
