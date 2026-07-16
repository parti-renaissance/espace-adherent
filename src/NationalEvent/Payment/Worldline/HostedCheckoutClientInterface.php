<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use App\Entity\NationalEvent\Payment;

interface HostedCheckoutClientInterface
{
    /**
     * Hosted Checkout session lifetime, in minutes (Worldline's own default is 180). Pinned explicitly so that the
     * double-checkout guard and the reconciliation expiry window derive from a single source of truth: expiring a
     * payment that Worldline can still capture would cancel an inscription already paid for.
     */
    public const SESSION_TIMEOUT_MINUTES = 180;

    public function createHostedCheckout(Payment $payment, string $returnUrl): CheckoutResult;

    public function getHostedCheckout(string $hostedCheckoutId): PaymentResult;

    /**
     * Fallback for a checkout whose session has expired: the hosted checkout is gone but the payment still resolves.
     */
    public function getPaymentDetails(string $paymentId): PaymentResult;
}
