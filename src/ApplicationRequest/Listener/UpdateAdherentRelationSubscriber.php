<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\Membership\AdherentEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Repository\ApplicationRequest\RunningMateRequestRepository;
use AppBundle\Repository\ApplicationRequest\VolunteerRequestRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateAdherentRelationSubscriber implements EventSubscriberInterface
{
    private $volunteerRepository;
    private $runningMateRepository;

    public function __construct(
        VolunteerRequestRepository $volunteerRepository,
        RunningMateRequestRepository $runningMateRepository
    ) {
        $this->volunteerRepository = $volunteerRepository;
        $this->runningMateRepository = $runningMateRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentRegistration', -1],
        ];
    }

    public function onAdherentRegistration(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        $this->runningMateRepository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
        $this->volunteerRepository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
    }
}
