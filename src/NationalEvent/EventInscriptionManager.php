<?php

namespace App\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\DTO\InscriptionRequest;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\UpdateNationalEventInscriptionEvent;
use App\NationalEvent\Payment\RequestParamsBuilder;
use App\PublicId\MeetingInscriptionPublicIdGenerator;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventInscriptionManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventInscriptionRepository $eventInscriptionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AdherentRepository $adherentRepository,
        private readonly RequestParamsBuilder $requestParamsBuilder,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly MeetingInscriptionPublicIdGenerator $meetingInscriptionPublicIdGenerator,
    ) {
    }

    public function saveInscription(NationalEvent $nationalEvent, InscriptionRequest $inscriptionRequest, ?EventInscription $existingInscription = null): EventInscription
    {
        $eventInscription = $existingInscription ?? new EventInscription($nationalEvent);
        $eventInscription->updateFromRequest($inscriptionRequest);

        if (!$eventInscription->getPublicId()) {
            $eventInscription->setPublicId($this->meetingInscriptionPublicIdGenerator->generate());
        }

        if ($adherent = $this->adherentRepository->findOneByEmail($eventInscription->addressEmail)) {
            $eventInscription->adherent = $adherent;
        }

        if (
            $eventInscription->referrerCode
            && !$eventInscription->referrer
            && $referrer = $this->adherentRepository->findByPublicId($eventInscription->referrerCode, true)
        ) {
            $eventInscription->referrer = $referrer;
        }

        $this->entityManager->persist($eventInscription);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch($existingInscription ? new UpdateNationalEventInscriptionEvent($eventInscription) : new NewNationalEventInscriptionEvent($eventInscription));

        return $eventInscription;
    }

    public function updatePackage(InscriptionRequest $inscriptionRequest, EventInscription $eventInscription): ?Payment
    {
        $newAmount = $eventInscription->event->calculateInscriptionAmount(
            $inscriptionRequest->transport,
            $inscriptionRequest->accommodation,
            $inscriptionRequest->withDiscount
        );

        if (!$newAmount) {
            $eventInscription->updateTransportFromRequest($inscriptionRequest);

            if (InscriptionStatusEnum::WAITING_PAYMENT === $eventInscription->status) {
                $eventInscription->status = InscriptionStatusEnum::PENDING;
            }
            $eventInscription->paymentStatus = null;

            $successPayments = $eventInscription->getSuccessPayments();
            array_walk($successPayments, static fn (Payment $payment) => $payment->markAsToRefund());

            $this->entityManager->flush();

            return null;
        }

        $paymentParams = $this->requestParamsBuilder->build(
            $uuid = Uuid::uuid4(),
            $newAmount,
            $eventInscription,
            $this->urlGenerator->generate('app_national_event_payment_status', ['slug' => $eventInscription->event->getSlug(), 'uuid' => $eventInscription->getUuid()->toString()], UrlGeneratorInterface::ABSOLUTE_URL),
        );

        $eventInscription->addPayment($payment = new Payment(
            $uuid,
            $eventInscription,
            $newAmount,
            $inscriptionRequest->visitDay,
            $inscriptionRequest->transport,
            $inscriptionRequest->accommodation,
            $inscriptionRequest->withDiscount,
            $paymentParams
        ));

        if (!$eventInscription->amount) {
            $eventInscription->amount = $newAmount;
            $eventInscription->visitDay = $inscriptionRequest->visitDay;
            $eventInscription->transport = $inscriptionRequest->transport;
            $eventInscription->accommodation = $inscriptionRequest->accommodation;
            $eventInscription->withDiscount = $inscriptionRequest->withDiscount;
        }

        $eventInscription->roommateIdentifier = $inscriptionRequest->roommateIdentifier;

        $this->entityManager->flush();

        return $payment;
    }

    public function countReservedPlaces(NationalEvent $event): array
    {
        return $this->eventInscriptionRepository->countPlacesByTransport($event->getId());
    }
}
