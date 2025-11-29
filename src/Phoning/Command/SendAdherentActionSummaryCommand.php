<?php

declare(strict_types=1);

namespace App\Phoning\Command;

use App\Entity\Phoning\CampaignHistory;

class SendAdherentActionSummaryCommand
{
    private CampaignHistory $campaignHistory;

    public function __construct(CampaignHistory $campaignHistory)
    {
        $this->campaignHistory = $campaignHistory;
    }

    public function getCampaignHistory(): CampaignHistory
    {
        return $this->campaignHistory;
    }
}
