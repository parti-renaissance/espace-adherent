<?php

declare(strict_types=1);

namespace App\Pap\Command;

use Ramsey\Uuid\UuidInterface;

class UpdateCampaignAddressInfoCommand implements AsynchronousMessageInterface
{
    private UuidInterface $campaignUuid;

    public function __construct(UuidInterface $campaignUuid)
    {
        $this->campaignUuid = $campaignUuid;
    }

    public function getCampaignUuid(): UuidInterface
    {
        return $this->campaignUuid;
    }
}
