<?php

declare(strict_types=1);

namespace App\AdherentMessage\Sender;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Manager;

class CampaignSender implements SenderInterface
{
    public function __construct(private readonly Manager $manager)
    {
    }

    public function supports(AdherentMessageInterface $message, bool $forTest): bool
    {
        return !$message->isStatutory();
    }

    public function send(AdherentMessageInterface $message, array $recipients = []): void
    {
        $this->manager->sendCampaign($message);
    }

    public function sendTest(AdherentMessageInterface $message, array $recipients = []): bool
    {
        return $this->manager->sendTestCampaign($message, array_map(function (Adherent $adherent): string {
            return $adherent->getEmailAddress();
        }, $recipients));
    }
}
