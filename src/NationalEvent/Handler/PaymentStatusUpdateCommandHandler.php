<?php

declare(strict_types=1);

namespace App\NationalEvent\Handler;

use App\Entity\NationalEvent\Payment;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\Payment\PaymentConfirmationApplier;
use App\NationalEvent\Payment\Worldline\WorldlineStatusMapper;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PaymentStatusUpdateCommandHandler
{
    private const CURRENCY_CODE = 'EUR';

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly WorldlineStatusMapper $statusMapper,
        private readonly PaymentConfirmationApplier $confirmationApplier,
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

        $this->confirmationApplier->apply($payment, $resolvedStatus, $payload, $worldlinePaymentId, $statusCode);
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
