<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

class CheckoutResult
{
    public function __construct(
        public readonly string $hostedCheckoutId,
        public readonly string $redirectUrl,
        public readonly ?string $returnMac = null,
    ) {
    }
}
