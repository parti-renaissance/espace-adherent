<?php

namespace App\Adherent\Referral\Listener;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\Renaissance\Adhesion\AdherentRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdherentRequestListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdherentRequestRepository $adherentRequestRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function updateAdherentRequests(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        /** @var AdherentRequest[] $adherentRequests */
        $adherentRequests = $this->adherentRequestRepository->findBy(['email' => $adherent->getEmailAddress()]);

        foreach ($adherentRequests as $adherentRequest) {
            $adherentRequest->email = null;
            $adherentRequest->uuid = $adherent->getUuid();
            $adherentRequest->adherent = $adherent;
        }

        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_CREATED => ['updateAdherentRequests', -257],
        ];
    }
}
