<?php

namespace AppBundle\Consumer;

class CampaignMailjetConsumer extends AbstractMailjetConsumer
{
    protected function getClientId(): string
    {
        return 'app.mailjet.campaign_client';
    }
}
