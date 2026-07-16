<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment;

use App\Entity\NationalEvent\Payment;
use App\Entity\NationalEvent\PaymentStatus;
use App\NationalEvent\Event\SuccessPaymentEvent;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Applies an already-resolved payment status to an inscription. Everything here is PSP-agnostic: the caller owns the
 * payload parsing, the status mapping and the payload-level security checks of its own PSP.
 */
class PaymentConfirmationApplier
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param array       $payload      the raw PSP payload, stored as-is for forensics
     * @param string|null $pspPaymentId the PSP payment reference, null on rails that have none (Paybox)
     * @param string|null $statusCode   the PSP status code, kept for traceability
     */
    public function apply(
        Payment $payment,
        PaymentStatusEnum $resolvedStatus,
        array $payload,
        ?string $pspPaymentId = null,
        ?string $statusCode = null,
    ): void {
        $inscription = $payment->inscription;
        $wasAlreadyConfirmed = $inscription->hasConfirmedPaymentForCurrentPackage();

        $paymentStatus = new PaymentStatus($payment, $payload);
        $paymentStatus->worldlinePaymentId = $pspPaymentId;
        $paymentStatus->statusCode = $statusCode;
        $paymentStatus->status = $resolvedStatus;

        $payment->worldlinePaymentId = $pspPaymentId;
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
}
