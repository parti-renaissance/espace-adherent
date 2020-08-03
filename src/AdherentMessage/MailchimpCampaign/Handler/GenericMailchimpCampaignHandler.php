<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;

class GenericMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    private const SUPPORTED_CLASS = [
        CitizenProjectAdherentMessage::class,
    ];

    public function handle(AdherentMessageInterface $message): void
    {
        if (1 !== \count($message->getMailchimpCampaigns())) {
            $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
        }
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return \in_array(\get_class($message), self::SUPPORTED_CLASS, true);
    }
}
