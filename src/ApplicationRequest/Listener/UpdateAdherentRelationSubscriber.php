<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\Membership\AdherentEvent;
use AppBundle\Membership\AdherentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateAdherentRelationSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(ApplicationRequestRepository $repository)
    {
        $this->repository = $repository;
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

        $this->repository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
    }
}
