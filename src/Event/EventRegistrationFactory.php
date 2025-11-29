<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Event\EventRegistration;
use App\Repository\AdherentRepository;

class EventRegistrationFactory
{
    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
    }

    public function createFromCommand(EventRegistrationCommand $command): EventRegistration
    {
        $registration = new EventRegistration(
            $command->getRegistrationUuid(),
            $command->getEvent(),
            $command->getFirstName(),
            $command->getLastName(),
            $command->getEmailAddress(),
            $command->getPostalCode(),
            $command->isJoinNewsletter(),
            $command->getAdherent(),
            $command->getAuthAppCode(),
            status: $command->status,
        );

        $registration->utmSource = $command->utmSource;
        $registration->utmCampaign = $command->utmCampaign;

        if ($registration->referrerCode = $command->referrerCode) {
            $registration->referrer = $this->adherentRepository->findByPublicId($registration->referrerCode, true);
        }

        return $registration;
    }
}
