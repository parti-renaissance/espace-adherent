<?php

namespace App\ApplicationRequest\Listener;

use App\ApplicationRequest\ApplicationRequestRepository;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Membership\Event\UserEmailEvent;
use App\Membership\UserEvents;
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

        if (null === $adherent->getSource() && $adherent->isAdherent()) {
            $this->repository->updateAdherentRelation($adherent->getEmailAddress(), $adherent);
        }
    }
}
