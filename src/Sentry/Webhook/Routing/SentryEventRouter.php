<?php

declare(strict_types=1);

namespace App\Sentry\Webhook\Routing;

use App\Sentry\Webhook\SentryEvent;

class SentryEventRouter
{
    /**
     * @param array<string, array<string, array{slack?: string, clickup?: string|null}>> $routingTable
     */
    public function __construct(
        private readonly array $routingTable,
        private readonly string $mobileProjectId,
        private readonly string $backendProjectId,
    ) {
    }

    public function route(SentryEvent $event): ?RoutingDecision
    {
        $category = $this->resolveCategory($event);

        if (null === $category) {
            return null;
        }

        $environment = null !== $event->environment ? mb_strtolower($event->environment) : '';

        if ('' === $environment) {
            return null;
        }

        $leaf = $this->routingTable[$category][$environment] ?? null;

        if (!\is_array($leaf)) {
            return null;
        }

        $slackChannelId = $this->normalizeChannel($leaf['slack'] ?? null);
        $clickUpChannelId = $this->normalizeChannel($leaf['clickup'] ?? null);

        if (null === $slackChannelId && null === $clickUpChannelId) {
            return null;
        }

        return new RoutingDecision(
            $category,
            $environment,
            $slackChannelId,
            $clickUpChannelId,
        );
    }

    private function normalizeChannel(?string $channelId): ?string
    {
        return null === $channelId || '' === $channelId ? null : $channelId;
    }

    private function resolveCategory(SentryEvent $event): ?string
    {
        if ($event->projectId === $this->mobileProjectId) {
            return 'mobile';
        }

        if ($event->projectId === $this->backendProjectId) {
            return match ($event->platform) {
                'php' => 'backend-php',
                'javascript' => 'backend-js',
                default => null,
            };
        }

        return null;
    }
}
