<?php

declare(strict_types=1);

namespace App\NationalEvent\Listener;

use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
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
            UserEvents::USER_CREATED => ['onAdherentRegistration', -10],
        ];
    }

    public function onAdherentRegistration(UserEvent $event): void
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
