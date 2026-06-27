<?php

declare(strict_types=1);

namespace Tests\App\Unit\Sentry\Webhook;

use App\Sentry\Webhook\SentryEventFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
class SentryEventFactoryTest extends TestCase
{
    private SentryEventFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SentryEventFactory();
    }

    public function testFromPayloadMapsRoutingFields(): void
    {
        $event = $this->factory->fromPayload(['data' => ['event' => [
            'project' => 4511585443381328,
            'platform' => 'php',
            'environment' => 'production',
            'issue_id' => '6543210',
            'title' => 'Boom',
            'culprit' => 'App\\Mailer\\Mailer::send',
            'level' => 'error',
            'web_url' => 'https://sentry.io/issues/6543210/',
        ]]]);

        self::assertSame('4511585443381328', $event->projectId);
        self::assertSame('php', $event->platform);
        self::assertSame('production', $event->environment);
        self::assertSame('6543210', $event->issueId);
        self::assertSame('https://sentry.io/issues/6543210/', $event->webUrl);
    }

    public function testFromPayloadFallsBackToTagsForEnvironment(): void
    {
        $event = $this->factory->fromPayload(['data' => ['event' => [
            'project' => 4511585504067664,
            'platform' => 'javascript',
            // no top-level "environment"
            'title' => 'log',
            'tags' => [['server_name', 'host'], ['environment', 'Staging']],
        ]]]);

        self::assertSame('staging', $event->environment);
    }
}
