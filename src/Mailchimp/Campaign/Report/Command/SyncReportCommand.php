<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Report\Command;

use App\Mailchimp\CampaignMessageInterface;
use App\Messenger\Message\AbstractUuidMessage;
use Ramsey\Uuid\UuidInterface;

class SyncReportCommand extends AbstractUuidMessage implements CampaignMessageInterface
{
    public function __construct(
        UuidInterface $uuid,
        public readonly bool $firstRun = false,
        public readonly bool $autoReschedule = true,
    ) {
        parent::__construct($uuid);
    }
}
