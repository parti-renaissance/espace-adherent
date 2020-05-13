<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;

interface MailchimpCampaignHandlerInterface
{
    public function handle(AdherentMessageInterface $message): void;

    public function supports(AdherentMessageInterface $message): bool;
}
