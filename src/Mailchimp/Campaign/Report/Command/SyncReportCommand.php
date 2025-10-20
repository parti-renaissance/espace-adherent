<?php

namespace App\Mailchimp\Campaign\Report\Command;

use App\Mailchimp\CampaignMessageInterface;
use App\Messenger\Message\AbstractUuidMessage;
use Ramsey\Uuid\UuidInterface;

class SyncReportCommand extends AbstractUuidMessage implements CampaignMessageInterface
{
    public function __construct(
        UuidInterface $uuid,
        public readonly bool $firstRun = false,
    ) {
        parent::__construct($uuid);
    }
}
