<?php

namespace AppBundle\Consumer\Mailjet;

use AppBundle\Mailjet\MailjetService;

class MailjetCampaignConsumer extends AbstractMailjetConsumer
{
    protected function getName(): string
    {
        return 'mailjet-campaign';
    }

    protected function getMailjet(): MailjetService
    {
        return $this->container->get('app.mailjet.client.campaign');
    }
}
