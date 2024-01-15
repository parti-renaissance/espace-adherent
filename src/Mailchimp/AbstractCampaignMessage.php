<?php

namespace App\Mailchimp;

abstract class AbstractCampaignMessage implements CampaignMessageInterface
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
