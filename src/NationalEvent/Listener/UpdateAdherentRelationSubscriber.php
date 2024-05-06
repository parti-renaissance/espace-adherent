<?php

namespace App\NationalEvent\Listener;

use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpdateAdherentRelationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EventInscriptionRepository $eventInscriptionRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentRegistration', -10],
        ];
    }

    public function onAdherentRegistration(AdherentEvent $event): void
    {
        $adherent = $event->getAdherent();

        $this->eventInscriptionRepository
            ->createQueryBuilder('ei')
            ->update()
            ->set('ei.adherent', ':adherent')
            ->where('ei.adherent IS NULL')
            ->andWhere('ei.addressEmail = :email')
            ->andWhere('ei.createdAt > :created_after')
            ->setParameters([
                'adherent' => $adherent,
                'email' => $adherent->getEmailAddress(),
                'created_after' => new \DateTime('-6 months'),
            ])
            ->getQuery()
            ->execute()
        ;
    }
}
