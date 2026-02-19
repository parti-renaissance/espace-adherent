<?php

declare(strict_types=1);

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PaymentStatusUpdateCommand $command): void
    {
        $payload = $command->payload;

        $paymentUuid = $payload['orderID'] ?? null;
        if (empty($paymentUuid) || !Uuid::isValid($paymentUuid)) {
            $this->logger->error('Invalid or missing orderID in payment webhook payload.');

            return;
        }

        $payment = $this->paymentRepository->findOneByUuid($paymentUuid);
        if (!$payment instanceof Payment) {
            $this->logger->error('Payment not found for UUID: {uuid}.', ['uuid' => $paymentUuid]);

            return;
        }

        foreach ($payment->getStatuses() as $existingStatus) {
            if ($existingStatus->payload === $payload) {
                $this->logger->info('Duplicate webhook payload for payment {uuid}, skipping.', ['uuid' => $paymentUuid]);

                return;
            }
        }

        $inscription = $payment->inscription;

        $paymentStatus = new PaymentStatus($payment, $payload);
        $payment->addStatus($paymentStatus);

        $isLastPayment = true;

        foreach ($inscription->getSuccessPayments() as $successPayment) {
            if ($successPayment !== $payment && $payment->getCreatedAt() < $successPayment->getCreatedAt()) {
                $isLastPayment = false;
            }

            if (!$paymentStatus->isSuccess() || $successPayment === $payment || $successPayment->toRefund) {
                continue;
            }

            $successPayment->markAsToRefund($payment);
        }

        if ($paymentStatus->isSuccess()) {
            if (InscriptionStatusEnum::WAITING_PAYMENT === $inscription->status) {
                $inscription->status = InscriptionStatusEnum::PENDING;
            }

            if ($isLastPayment) {
                $inscription->packageValues = $payment->packageValues;

                if ($inscription->withDiscount !== $payment->withDiscount) {
                    $inscription->withDiscount = $payment->withDiscount;
                }

                if ($inscription->amount !== $payment->amount) {
                    $inscription->amount = $payment->amount;
                }
            }
        }

        if ($inscription->isCurrentPayment($payment) && ($isLastPayment || PaymentStatusEnum::PENDING === $inscription->paymentStatus)) {
            $inscription->paymentStatus = match ($paymentStatus->getStatus()) {
                PaymentStatusEnum::CONFIRMED, PaymentStatusEnum::REFUNDED => $paymentStatus->getStatus(),
                default => PaymentStatusEnum::ERROR,
            };
        }

        $this->entityManager->flush();

        if ($paymentStatus->isSuccess()) {
            $this->eventDispatcher->dispatch(new SuccessPaymentEvent($inscription));
        }
    }
}
