<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SnsSubscriptionConfirmer
{
    private const SNS_HOST_PATTERN = '/^sns\.[a-z0-9-]+\.amazonaws\.com$/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function confirm(string $subscribeUrl): bool
    {
        if (!$this->isSnsEndpoint($subscribeUrl)) {
            $this->logger->error('[SES][Webhook] Refused to confirm subscription: SubscribeURL is not an SNS endpoint', [
                'subscribe_host' => parse_url($subscribeUrl, \PHP_URL_HOST),
            ]);

            return false;
        }

        try {
            $statusCode = $this->httpClient->request('GET', $subscribeUrl)->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('[SES][Webhook] SNS subscription confirmation failed', ['exception' => $e]);

            return false;
        }

        if (200 !== $statusCode) {
            $this->logger->error('[SES][Webhook] SNS refused the subscription confirmation', ['status_code' => $statusCode]);

            return false;
        }

        return true;
    }

    private function isSnsEndpoint(string $url): bool
    {
        $parts = parse_url($url);

        if (!\is_array($parts) || !isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        return 'https' === $parts['scheme'] && 1 === preg_match(self::SNS_HOST_PATTERN, $parts['host']);
    }
}
