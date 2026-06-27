<?php

declare(strict_types=1);

namespace App\Sentry\Webhook;

class SentryEvent
{
    public function __construct(
        public readonly string $projectId,
        public readonly ?string $platform,
        public readonly ?string $environment,
        public readonly string $issueId,
        public readonly string $title,
        public readonly ?string $culprit,
        public readonly ?string $level,
        public readonly ?string $webUrl,
    ) {
    }
}
