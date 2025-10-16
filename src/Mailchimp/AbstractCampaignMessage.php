<?php

namespace App\Mailchimp;

use App\Messenger\Message\LockableMessageInterface;

abstract class AbstractCampaignMessage implements CampaignMessageInterface, LockableMessageInterface
{
    public function getLockTtl(): int
    {
        return 60;
    }

    public function isLockBlocking(): bool
    {
        return true;
    }
}
