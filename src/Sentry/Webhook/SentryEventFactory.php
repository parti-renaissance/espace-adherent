<?php

declare(strict_types=1);

namespace App\Sentry\Webhook;

class SentryEventFactory
{
    /**
     * @param array<string, mixed> $payload
     */
    public function fromPayload(array $payload): SentryEvent
    {
        $event = $payload['data']['event'] ?? [];

        if (!\is_array($event)) {
            $event = [];
        }

        return new SentryEvent(
            projectId: isset($event['project']) && \is_scalar($event['project']) ? (string) $event['project'] : '',
            platform: \is_string($event['platform'] ?? null) ? $event['platform'] : null,
            environment: $this->extractEnvironment($event),
            issueId: isset($event['issue_id']) && \is_scalar($event['issue_id']) ? (string) $event['issue_id'] : '',
            title: \is_string($event['title'] ?? null) ? $event['title'] : '',
            culprit: \is_string($event['culprit'] ?? null) ? $event['culprit'] : null,
            level: \is_string($event['level'] ?? null) ? $event['level'] : null,
            webUrl: \is_string($event['web_url'] ?? null) ? $event['web_url'] : null,
        );
    }

    private function extractEnvironment(array $event): ?string
    {
        if (\is_string($event['environment'] ?? null) && '' !== $event['environment']) {
            return mb_strtolower($event['environment']);
        }

        foreach ($event['tags'] ?? [] as $tag) {
            if (\is_array($tag) && 'environment' === ($tag[0] ?? null) && \is_string($tag[1] ?? null)) {
                return mb_strtolower($tag[1]);
            }
        }

        return null;
    }
}
