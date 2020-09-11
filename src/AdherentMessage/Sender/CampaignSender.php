<?php

namespace App\AdherentMessage\Sender;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CampaignAdherentMessageInterface;
use App\Mailchimp\Manager;

class CampaignSender implements SenderInterface
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return $message instanceof CampaignAdherentMessageInterface;
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->manager->sendCampaign($message);
    }

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->manager->sendTestCampaign($message, array_map(function (Adherent $adherent): string {
            return $adherent->getEmailAddress();
        }, $recipients));
    }

    public function renderMessage(AdherentMessageInterface $message, array $recipients = []): string
    {
        return $this->manager->getCampaignContent(current($message->getMailchimpCampaigns()));
    }
}
