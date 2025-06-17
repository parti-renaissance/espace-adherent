<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class PaymentStatusUpdateCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EventInscriptionRepository $inscriptionRepository,
    ) {
    }

    public function __invoke(PaymentStatusUpdateCommand $command): void
    {
        $payload = $command->payload;

        if (empty($inscriptionUuid = ($payload['COMPLUS'] ?? null)) || !Uuid::isValid($inscriptionUuid)) {
            return;
        }

        $inscription = $this->inscriptionRepository->findOneByUuid($inscriptionUuid);

        if (!$inscription instanceof EventInscription) {
            return;
        }

        $inscription->addPaymentStatus($paymentStatus = new PaymentStatus($inscription, $payload));

        $this->entityManager->flush();

        if ($paymentStatus->isSuccess() && InscriptionStatusEnum::WAITING_PAYMENT === $inscription->status) {
            $inscription->status = InscriptionStatusEnum::PAYMENT_CONFIRMED;

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new SuccessPaymentEvent($inscription));
        }
    }
}
