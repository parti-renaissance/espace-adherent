<?php

declare(strict_types=1);

namespace App\Pap\Command;

use Symfony\Component\Uid\Uuid;

class UpdateCampaignAddressInfoCommand implements AsynchronousMessageInterface
{
    private Uuid $campaignUuid;

    public function __construct(Uuid $campaignUuid)
    {
        $this->campaignUuid = $campaignUuid;
    }

    public function getCampaignUuid(): Uuid
    {
        return $this->campaignUuid;
    }
}
