<?php

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
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
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function __invoke(PaymentStatusUpdateCommand $command): void
    {
        $payload = $command->payload;

        if (empty($paymentUuid = ($payload['orderID'] ?? null)) || !Uuid::isValid($paymentUuid)) {
            return;
        }

        $payment = $this->paymentRepository->findOneByUuid($paymentUuid);
        if (!$payment instanceof Payment) {
            return;
        }

        $inscription = $payment->inscription;

        $payment->addStatus($paymentStatus = new PaymentStatus($payment, $payload));

        $this->entityManager->flush();

        if ($paymentStatus->isSuccess() && InscriptionStatusEnum::WAITING_PAYMENT === $inscription->status) {
            $inscription->status = InscriptionStatusEnum::PAYMENT_CONFIRMED;

            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new SuccessPaymentEvent($inscription));
        }
    }
}
