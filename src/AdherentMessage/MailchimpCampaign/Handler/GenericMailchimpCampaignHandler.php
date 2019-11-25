<?php

namespace AppBundle\AdherentMessage\MailchimpCampaign\Handler;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use AppBundle\Entity\AdherentMessage\SenatorAdherentMessage;

class GenericMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    private const SUPPORTED_CLASS = [
        DeputyAdherentMessage::class,
        SenatorAdherentMessage::class,
        CitizenProjectAdherentMessage::class,
        MunicipalChiefAdherentMessage::class,
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
