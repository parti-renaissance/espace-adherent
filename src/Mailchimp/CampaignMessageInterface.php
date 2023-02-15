<?php

namespace App\Mailchimp;

use App\Messenger\Message\LockableMessageInterface;

/**
 * Interface implements by command classes for editing Mailchimp campaigns
 */
interface CampaignMessageInterface extends LockableMessageInterface
{
}
