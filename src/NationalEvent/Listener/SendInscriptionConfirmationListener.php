<?php

namespace App\NationalEvent\Listener;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\Event\NewNationalEventInscriptionEvent;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\Notifier;
use App\Repository\Geo\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendInscriptionConfirmationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Notifier $notifier,
        private readonly ZoneRepository $zoneRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NewNationalEventInscriptionEvent::class => 'onNewInscription',
            SuccessPaymentEvent::class => 'onSuccessPayment',
        ];
    }

    public function onNewInscription(NewNationalEventInscriptionEvent $event): void
    {
        $eventInscription = $event->getEventInscription();

        if (!$eventInscription->isDuplicate() && !$eventInscription->amount) {
            $this->sendConfirmationEmail($eventInscription);
        }
    }

    public function onSuccessPayment(SuccessPaymentEvent $event): void
    {
        $this->sendConfirmationEmail($event->eventInscription);
    }

    private function sendConfirmationEmail(EventInscription $eventInscription): void
    {
        if ($eventInscription->confirmationSentAt) {
            return;
        }

        $departmentCode = $eventInscription->postalCode ? substr($eventInscription->postalCode, 0, 2) : null;

        $zone = [];
        if ($departmentCode) {
            $departments = $this->zoneRepository->findAllDepartmentsIndexByCode([$departmentCode]);
            $zone = $departments[$departmentCode] ?? [];
        }

        $this->notifier->sendInscriptionConfirmation($eventInscription, $zone);

        $eventInscription->confirmationSentAt = new \DateTime();

        $this->entityManager->flush();
    }
}
