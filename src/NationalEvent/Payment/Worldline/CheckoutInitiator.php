<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use App\Entity\NationalEvent\Payment;
use Doctrine\ORM\EntityManagerInterface;

class CheckoutInitiator
{
    public function __construct(
        private readonly HostedCheckoutClientInterface $hostedCheckoutClient,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Returns the Worldline URL the payer must be redirected to.
     */
    public function initiate(Payment $payment, string $returnUrl): string
    {
        if (null !== $redirectUrl = $this->findLiveCheckoutRedirectUrl($payment)) {
            return $redirectUrl;
        }

        $result = $this->hostedCheckoutClient->createHostedCheckout($payment, $returnUrl);

        $payment->hostedCheckoutId = $result->hostedCheckoutId;
        $payment->payload = [
            'hostedCheckoutId' => $result->hostedCheckoutId,
            'redirectUrl' => $result->redirectUrl,
            'returnMac' => $result->returnMac,
            'createdAt' => new \DateTimeImmutable()->format(\DATE_ATOM),
        ];

        $this->entityManager->flush();

        return $result->redirectUrl;
    }

    /**
     * A payment stays pending after a first checkout is opened, so a double submit or a browser back button would open
     * a second session for the same payment. Reuse the session already opened until it expires.
     */
    private function findLiveCheckoutRedirectUrl(Payment $payment): ?string
    {
        $redirectUrl = $payment->payload['redirectUrl'] ?? null;
        $createdAt = $payment->payload['createdAt'] ?? null;

        if (null === $payment->hostedCheckoutId || !\is_string($redirectUrl) || !\is_string($createdAt)) {
            return null;
        }

        try {
            $expiresAt = new \DateTimeImmutable($createdAt)
                ->modify(\sprintf('+%d minutes', HostedCheckoutClientInterface::SESSION_TIMEOUT_MINUTES))
            ;
        } catch (\Exception) {
            return null;
        }

        return $expiresAt > new \DateTimeImmutable() ? $redirectUrl : null;
    }
}
