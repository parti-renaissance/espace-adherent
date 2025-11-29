<?php

declare(strict_types=1);

namespace App\Mailchimp\Event;

use App\Entity\AdherentMessage\MailchimpCampaign;
use Symfony\Contracts\EventDispatcher\Event;

class CampaignEvent extends Event
{
    private $campaign;

    public function __construct(MailchimpCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function getCampaign(): MailchimpCampaign
    {
        return $this->campaign;
    }
}
