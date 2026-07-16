<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use App\Entity\NationalEvent\Payment;
use App\NationalEvent\Command\PaymentStatusUpdateCommand;
use App\NationalEvent\PaymentStatusEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CheckoutOutcomeResolver
{
    public function __construct(
        private readonly HostedCheckoutClientInterface $hostedCheckoutClient,
        private readonly WorldlineStatusMapper $statusMapper,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Asks Worldline for the outcome and schedules its persistence.
     *
     * The status is returned from the API response rather than read back from the payment: the status command is
     * asynchronous, so in production the entity is still stale when the payer lands back on the return page.
     */
    public function resolve(Payment $payment): PaymentStatusEnum
    {
        $result = $this->fetch($payment);

        if (null === $result || $result->isEmpty()) {
            return PaymentStatusEnum::UNKNOWN;
        }

        $this->bus->dispatch(new PaymentStatusUpdateCommand($result->rawPayment));

        return $this->statusMapper->map($result->rawPayment);
    }

    private function fetch(Payment $payment): ?PaymentResult
    {
        try {
            if (null !== $payment->hostedCheckoutId) {
                $result = $this->hostedCheckoutClient->getHostedCheckout($payment->hostedCheckoutId);

                if (!$result->isEmpty()) {
                    return $result;
                }
            }

            // Once the checkout session has expired the hosted checkout is gone, but the payment still resolves.
            if (null !== $payment->worldlinePaymentId) {
                return $this->hostedCheckoutClient->getPaymentDetails($payment->worldlinePaymentId);
            }
        } catch (\Throwable $e) {
            // Degrade to an unknown outcome rather than fail the return page: the webhook stays the source of truth.
            $this->logger->error('Unable to resolve Worldline outcome for payment {uuid}: {reason}.', [
                'uuid' => $payment->getUuid()->toRfc4122(),
                'reason' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
