<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use OnlinePayments\Sdk\Webhooks\InMemorySecretKeyStore;
use OnlinePayments\Sdk\Webhooks\WebhooksHelper;

/**
 * Verifies the signature of an incoming Worldline webhook and extracts its payment event.
 *
 * The SDK owns the signature contract: the X-GCS-Signature header holds base64(hmac-sha256(raw body, secret)) and
 * X-GCS-KeyId selects the secret. The raw body must be passed untouched, any re-encoding breaks the signature.
 */
class WorldlineWebhookVerifier
{
    private const EVENT_TYPE_PREFIX = 'payment.';

    public function __construct(
        private readonly string $worldlineWebhookId,
        private readonly string $worldlineWebhookSecret,
    ) {
    }

    /**
     * @throws \OnlinePayments\Sdk\Webhooks\SignatureValidationException when the signature does not match
     * @throws \OnlinePayments\Sdk\Webhooks\ApiVersionMismatchException  when the event is not a v1 event
     */
    public function verifyAndExtract(string $rawBody, array $headers): ?WebhookEvent
    {
        $helper = new WebhooksHelper(new InMemorySecretKeyStore([
            $this->worldlineWebhookId => $this->worldlineWebhookSecret,
        ]));

        $event = $helper->unmarshal($rawBody, $this->flattenHeaders($headers));

        $payment = $event->getPayment();

        // Worldline emits events for refunds, payouts and tokens on the same endpoint; only payment ones concern us.
        if (null === $payment || !str_starts_with((string) $event->type, self::EVENT_TYPE_PREFIX)) {
            return null;
        }

        return new WebhookEvent(
            (string) $event->type,
            $event->merchantId,
            json_decode($payment->toJson(), true, 512, \JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Symfony exposes every header as a list of values while the SDK compares the raw scalar, which would make it
     * hash_equals() an array against a string.
     */
    private function flattenHeaders(array $headers): array
    {
        return array_map(static function (mixed $value): mixed {
            return \is_array($value) ? ($value[0] ?? '') : $value;
        }, $headers);
    }
}
