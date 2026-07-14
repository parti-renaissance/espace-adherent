<?php

declare(strict_types=1);

namespace App\Analytics\PostHog;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Entity\Adherent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Capture server-side événements PostHog + build super-properties.
 *
 * Feature flag POSTHOG_ENABLED : skip complet si false (dev/staging/preview).
 * Endpoint POST direct vers PostHog EU (pas via proxy — server-side, pas de blocage ITP).
 * Timeout 3s (server-side, plus court que proxy client 5s).
 * Silent fail loggé via LoggerInterface (jamais bloquer un flow métier).
 *
 * Fallback 'local'/'unknown' pour deploy_sha/deploy_version vides
 * (review Opus C4 — env vars pas encore injectées en dev).
 *
 * Cf. spec §7 + §10.
 */
class PostHogService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%posthog.api_host%')]
        private readonly string $apiHost,
        #[Autowire('%posthog.api_key%')]
        private readonly string $apiKey,
        #[Autowire('%posthog.enabled%')]
        private readonly bool $enabled,
        private readonly SiteContext $context,
        private readonly HashEmailService $hashEmail,
        #[Autowire('%env(APP_ENVIRONMENT)%')]
        private readonly string $environment,
        #[Autowire('%posthog.deploy_sha%')]
        private readonly ?string $deploySha,
        #[Autowire('%posthog.deploy_version%')]
        private readonly ?string $deployVersion,
        private readonly LoggerInterface $logger,
    ) {
    }

    /** @return array<string, mixed> */
    public function buildSuperProperties(): array
    {
        return [
            'site' => $this->context->getSite(),
            'platform' => 'web',
            'environment' => $this->environment,
            'deploy_sha' => substr($this->deploySha ?: 'local', 0, 7),
            'deploy_version' => $this->deployVersion ?: 'unknown',
            'locale' => 'fr-FR',
            'is_bot' => false,
        ];
    }

    /** @param array<string, mixed> $properties */
    public function captureServerSide(
        PostHogEventName $event,
        array $properties,
        ?Adherent $user = null,
    ): void {
        if (!$this->enabled || !$this->context->isInitialized()) {
            return;
        }

        $distinctId = $user?->getEmailAddress()
            ? $this->hashEmail->hash($user->getEmailAddress())
            : 'anonymous-server';

        $this->postCapture($event->value, $distinctId, $properties);
    }

    /**
     * Cas 2 server-side : capture avec $set.email autorisé (newsletter, pétition).
     * Whitelist stricte : uniquement pour les events NEWSLETTER_*_SERVER et PETITION_SIGNED_SERVER.
     *
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $set        Payload $set (peut contenir 'email' pour Cas 2)
     */
    public function captureServerSideWithSet(
        PostHogEventName $event,
        array $properties,
        array $set,
        string $distinctId,
    ): void {
        if (!$this->enabled || !$this->context->isInitialized()) {
            return;
        }
        $properties['$set'] = $set;
        $this->postCapture($event->value, $distinctId, $properties);
    }

    /** @param array<string, mixed> $properties */
    private function postCapture(string $eventName, string $distinctId, array $properties): void
    {
        $payload = [
            'api_key' => $this->apiKey,
            'event' => $eventName,
            'distinct_id' => $distinctId,
            'timestamp' => (new \DateTimeImmutable)->format(\DATE_ATOM),
            'properties' => array_merge($this->buildSuperProperties(), $properties),
        ];

        try {
            $this->httpClient->request(
                'POST',
                \sprintf('%s/capture/', $this->apiHost),
                [
                    'body' => json_encode($payload, \JSON_THROW_ON_ERROR),
                    'headers' => ['Content-Type' => 'application/json'],
                    'timeout' => 3.0,
                ],
            );
        } catch (\Throwable $e) {
            // Ne jamais bloquer un flow métier — silent fail loggué
            $this->logger->warning('PostHog captureServerSide failed', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
