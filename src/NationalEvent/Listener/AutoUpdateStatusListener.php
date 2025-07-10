<?php

namespace App\NationalEvent\Listener;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
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
        return [
            NewNationalEventInscriptionEvent::class => 'autoUpdateStatus',
            SuccessPaymentEvent::class => 'onSuccessPayment',
        ];
    }

    public function autoUpdateStatus(NewNationalEventInscriptionEvent $event): void
    {
        $newEventInscription = $event->eventInscription;

        // Duplicate
        if ($oldEventInscription = $this->eventInscriptionRepository->findDuplicate($newEventInscription)) {
            // Mark as duplicate the new inscription
            if (!$newEventInscription->amount || $oldEventInscription->isPaymentSuccess()) {
                $newEventInscription->status = InscriptionStatusEnum::DUPLICATE;
                $newEventInscription->paymentStatus = null;
                $oldEventInscription->updateFromDuplicate($newEventInscription);

                $newEventInscription->originalInscription = $oldEventInscription;

                $this->entityManager->flush();

                return;
            }

            // Mark as duplicate the old inscription
            if (
                $newEventInscription->amount > 0
                && (
                    PaymentStatusEnum::PENDING === $oldEventInscription->paymentStatus
                    || !$oldEventInscription->amount
                )
            ) {
                $this->markAsDuplicatedOldInscription($oldEventInscription, $newEventInscription);

                return;
            }
        }

        $this->acceptInscription($newEventInscription);
    }

    public function onSuccessPayment(SuccessPaymentEvent $event): void
    {
        $eventInscription = $event->eventInscription;

        if (!$eventInscription->isPaymentSuccess()) {
            return;
        }

        $this->acceptInscription($eventInscription);

        while ($oldInscription = $this->eventInscriptionRepository->findDuplicate($eventInscription)) {
            $this->markAsDuplicatedOldInscription($oldInscription, $eventInscription);
        }
    }

    public function markAsDuplicatedOldInscription(EventInscription $oldEventInscription, EventInscription $newEventInscription): void
    {
        $oldStatus = $oldEventInscription->status;

        $oldEventInscription->status = InscriptionStatusEnum::DUPLICATE;
        $oldEventInscription->paymentStatus = null;

        if (
            \in_array($oldStatus, [InscriptionStatusEnum::ACCEPTED, InscriptionStatusEnum::INCONCLUSIVE, InscriptionStatusEnum::REFUSED], true)
            && !\in_array($newEventInscription->status, [InscriptionStatusEnum::REFUSED, InscriptionStatusEnum::CANCELED, InscriptionStatusEnum::DUPLICATE], true)
        ) {
            $newEventInscription->status = $oldStatus;
            $newEventInscription->duplicateInscriptionForStatus = $oldEventInscription;
        }

        $this->entityManager->flush();
    }

    private function acceptInscription(EventInscription $eventInscription): void
    {
        if (
            PaymentStatusEnum::PENDING === $eventInscription->paymentStatus
            || InscriptionStatusEnum::PENDING !== $eventInscription->status
            || !($adherent = $eventInscription->adherent)
        ) {
            return;
        }

        if (
            ($adherent->isRenaissanceAdherent() && $adherent->getFirstMembershipDonation() && $adherent->getFirstMembershipDonation() < new \DateTime(date('Y-01-01')))
            || ($adherent->isRenaissanceSympathizer() && \count($this->eventInscriptionRepository->findAcceptedByEmail($eventInscription->addressEmail, $eventInscription->event)))
            || ($adherent->isRenaissanceAdherent() && (
                \count($adherent->getZoneBasedRoles())
                || !$adherent->getReceivedDelegatedAccesses()->isEmpty()
                || \count($adherent->findElectedRepresentativeMandates(true))
            ))
        ) {
            $eventInscription->status = InscriptionStatusEnum::ACCEPTED;

            $this->entityManager->flush();
        }
    }
}
