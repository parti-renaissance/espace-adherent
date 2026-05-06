<?php

declare(strict_types=1);

namespace App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageDeleteCommand;
use App\Mailchimp\Manager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AdherentMessageDeleteCommandHandler
{
    public function __construct(private readonly Manager $mailchimpManager)
    {
    }

    public function __invoke(AdherentMessageDeleteCommand $command): void
    {
        $this->mailchimpManager->deleteCampaign($command->getCampaignId());

        if ($staticSegmentId = $command->getStaticSegmentId()) {
            $this->mailchimpManager->deleteStaticSegment($staticSegmentId);
        }
    }
}
