<?php

declare(strict_types=1);

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;

class GenericMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    public static function getPriority(): int
    {
        return -255;
    }

    public function handle(AdherentMessageInterface $message): void
    {
        if (!\count($message->getMailchimpCampaigns())) {
            $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
        }
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return true;
    }
}
