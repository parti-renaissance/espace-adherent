<?php

declare(strict_types=1);

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\Payment\Worldline\WorldlineStatusMapper;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
class PaymentStatusUpdateCommandHandler
{
    private const CURRENCY_CODE = 'EUR';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PaymentRepository $paymentRepository,
        private readonly WorldlineStatusMapper $statusMapper,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(PaymentStatusUpdateCommand $command): void
    {
        $payload = $command->payload;

        $merchantReference = $command->getMerchantReference();
        if (null === $merchantReference) {
            $this->logger->error('Invalid or missing merchantReference in Worldline payment payload.');

            return;
        }

        $payment = $this->paymentRepository->findOneByUuid($merchantReference);
        if (!$payment instanceof Payment) {
            $this->logger->error('Payment not found for UUID: {uuid}.', ['uuid' => $merchantReference]);

            return;
        }

        $worldlinePaymentId = \is_string($payload['id'] ?? null) ? $payload['id'] : null;
        $statusCode = isset($payload['statusOutput']['statusCode']) ? (string) $payload['statusOutput']['statusCode'] : null;

        if ($this->isDuplicate($payment, $worldlinePaymentId, $statusCode)) {
            $this->logger->info('Duplicate Worldline event for payment {uuid}, skipping.', ['uuid' => $merchantReference]);

            return;
        }

        $resolvedStatus = $this->statusMapper->map($payload);

        // Security boundary: a payload correlated by merchant reference alone could confirm a payment for the wrong
        // amount. Never let a mismatching payload mark a payment as paid.
        if (PaymentStatusEnum::CONFIRMED === $resolvedStatus && !$this->matchesLocalPayment($payment, $payload)) {
            return;
        }

        $inscription = $payment->inscription;
        $wasAlreadyConfirmed = $inscription->hasConfirmedPaymentForCurrentPackage();

        $paymentStatus = new PaymentStatus($payment, $payload);
        $paymentStatus->worldlinePaymentId = $worldlinePaymentId;
        $paymentStatus->statusCode = $statusCode;
        $paymentStatus->status = $resolvedStatus;

        $payment->worldlinePaymentId = $worldlinePaymentId;
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
            $newPaymentStatus = match ($resolvedStatus) {
                PaymentStatusEnum::CONFIRMED, PaymentStatusEnum::REFUNDED => $resolvedStatus,
                // An authorised-but-not-yet-captured payment is still in flight: turning it into an error here would
                // reject an inscription that is about to be paid.
                PaymentStatusEnum::PENDING => PaymentStatusEnum::PENDING,
                default => PaymentStatusEnum::ERROR,
            };

            // A late event must not send a payer whose current package is already paid back to the checkout. Only a
            // refund moves such an inscription forward; a package change resets it through updateFromRequest().
            if (!$wasAlreadyConfirmed || PaymentStatusEnum::REFUNDED === $newPaymentStatus) {
                $inscription->paymentStatus = $newPaymentStatus;
            }
        }

        $this->entityManager->flush();

        // The browser return and the webhook both report the same success: only the first one may notify.
        if ($paymentStatus->isSuccess() && !$wasAlreadyConfirmed) {
            $this->eventDispatcher->dispatch(new SuccessPaymentEvent($inscription));
        }
    }

    private function isDuplicate(Payment $payment, ?string $worldlinePaymentId, ?string $statusCode): bool
    {
        if (null === $worldlinePaymentId || null === $statusCode) {
            return false;
        }

        foreach ($payment->getStatuses() as $existingStatus) {
            if ($existingStatus->worldlinePaymentId === $worldlinePaymentId && $existingStatus->statusCode === $statusCode) {
                return true;
            }
        }

        return false;
    }

    private function matchesLocalPayment(Payment $payment, array $payload): bool
    {
        $amount = $payload['paymentOutput']['amountOfMoney']['amount'] ?? null;
        $currency = $payload['paymentOutput']['amountOfMoney']['currencyCode'] ?? null;

        if ($amount !== $payment->amount) {
            $this->logger->error('Worldline amount mismatch for payment {uuid}: got {got}, expected {expected}.', [
                'uuid' => $payment->getUuid()->toRfc4122(),
                'got' => $amount,
                'expected' => $payment->amount,
            ]);

            return false;
        }

        if (self::CURRENCY_CODE !== $currency) {
            $this->logger->error('Worldline currency mismatch for payment {uuid}: got {got}.', [
                'uuid' => $payment->getUuid()->toRfc4122(),
                'got' => $currency,
            ]);

            return false;
        }

        return true;
    }
}
