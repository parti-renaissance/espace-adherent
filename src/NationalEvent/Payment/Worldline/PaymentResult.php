<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

class PaymentResult
{
    /**
     * @param array $rawPayment the payment payload as returned by Worldline, dispatched as-is to the status handler
     */
    public function __construct(
        public readonly ?string $paymentId,
        public readonly ?int $statusCode,
        public readonly ?string $status,
        public readonly ?int $amount,
        public readonly ?string $currency,
        public readonly array $rawPayment = [],
    ) {
    }

    public function isEmpty(): bool
    {
        return [] === $this->rawPayment;
    }
}
