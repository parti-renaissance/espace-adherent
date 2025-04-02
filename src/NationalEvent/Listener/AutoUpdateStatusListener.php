<?php

namespace App\NationalEvent\Listener;

use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoUpdateStatusListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [NewNationalEventInscriptionEvent::class => 'autoUpdateStatus'];
    }

    public function autoUpdateStatus(NewNationalEventInscriptionEvent $event): void
    {
        $newEventInscription = $event->eventInscription;

        // Duplicate
        if ($oldEventInscription = $this->eventInscriptionRepository->findDuplicate($newEventInscription)) {
            $newEventInscription->status = InscriptionStatusEnum::DUPLICATE;
            $oldEventInscription->updateFromDuplicate($newEventInscription);

            $this->entityManager->flush();

            return;
        }

        if (InscriptionStatusEnum::PENDING !== $newEventInscription->status || !$adherent = $newEventInscription->adherent) {
            return;
        }

        // Accepted
        if (
            ($adherent->isRenaissanceAdherent() && $adherent->getFirstMembershipDonation() && $adherent->getFirstMembershipDonation() < new \DateTime(date('Y-01-01')))
            || ($adherent->isRenaissanceSympathizer() && \count($this->eventInscriptionRepository->findAcceptedByEmail($newEventInscription->addressEmail, $newEventInscription->event)))
            || ($adherent->isRenaissanceAdherent() && (
                \count($adherent->getZoneBasedRoles())
                || !$adherent->getReceivedDelegatedAccesses()->isEmpty()
                || \count($adherent->findElectedRepresentativeMandates(true))
            ))
        ) {
            $newEventInscription->status = InscriptionStatusEnum::ACCEPTED;

            $this->entityManager->flush();
        }
    }
}
