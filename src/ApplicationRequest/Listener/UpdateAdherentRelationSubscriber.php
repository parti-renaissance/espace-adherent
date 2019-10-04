<?php

namespace AppBundle\ApplicationRequest\Listener;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\Membership\AdherentEvent;
use AppBundle\Membership\AdherentEvents;
use AppBundle\Membership\UserEmailEvent;
use AppBundle\Membership\UserEvents;
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
            UserEvents::USER_EMAIL_UPDATED => ['onAdherentEmailUpdate', -1],
        ];
    }

    public function onAdherentEmailUpdate(UserEmailEvent $event): void
    {
        $adherent = $event->getUser();

        $this->repository->updateAdherentRelation($event->getOldEmail(), null);
        $this->repository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
    }

    public function onAdherentRegistration(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        $this->repository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
    }
}
