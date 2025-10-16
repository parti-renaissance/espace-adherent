<?php

namespace App\Mailchimp\Campaign\Report\Command;

use App\Mailchimp\CampaignMessageInterface;
use App\Messenger\Message\AbstractUuidMessage;

class SyncReportCommand extends AbstractUuidMessage implements CampaignMessageInterface
{
}
