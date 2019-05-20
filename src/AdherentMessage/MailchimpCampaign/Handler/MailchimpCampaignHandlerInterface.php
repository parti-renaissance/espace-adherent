<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;

interface MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void;

    public function supports(AdherentMessageInterface $message): bool;
}
