<?php

declare(strict_types=1);

namespace App\Sentry\Webhook\Routing;

class RoutingDecision
{
    public function __construct(
        public readonly string $category,
        public readonly string $environment,
        public readonly ?string $slackChannelId,
        public readonly ?string $clickUpChannelId,
    ) {
    }
}
