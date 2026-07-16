<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

class WebhookEvent
{
    /**
     * @param array $payment the payment payload, dispatched as-is to the status handler
     */
    public function __construct(
        public readonly string $type,
        public readonly ?string $merchantId,
        public readonly array $payment,
    ) {
    }
}
