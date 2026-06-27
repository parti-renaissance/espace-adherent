<?php

declare(strict_types=1);

namespace Tests\App\Unit\Sentry\Webhook\Notifier;

use App\ClickUp\Notifier\ClickUpOptions;
use App\Sentry\Webhook\Notifier\SentryChatMessageFactory;
use App\Sentry\Webhook\Routing\RoutingDecision;
use App\Sentry\Webhook\SentryEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
class SentryChatMessageFactoryTest extends TestCase
{
    public function testCreateBuildsSlackAndClickUpMessagesWhenChannelMapped(): void
    {
        $messages = new SentryChatMessageFactory()->create(
            $this->event(),
            new RoutingDecision('backend-php', 'production', 'C_SLACK', 'CH_CLICKUP'),
        );

        self::assertCount(2, $messages);
        self::assertSame('slack', $messages[0]->getTransport());
        self::assertSame('clickup', $messages[1]->getTransport());

        $clickUpOptions = $messages[1]->getOptions();
        self::assertInstanceOf(ClickUpOptions::class, $clickUpOptions);
        self::assertSame('CH_CLICKUP', $clickUpOptions->getRecipientId());
    }

    public function testCreateBuildsSlackMessageOnlyWhenNoClickUpChannel(): void
    {
        $messages = new SentryChatMessageFactory()->create(
            $this->event(),
            new RoutingDecision('backend-php', 'production', 'C_SLACK', null),
        );

        self::assertCount(1, $messages);
        self::assertSame('slack', $messages[0]->getTransport());
    }

    public function testCreateBuildsClickUpMessageOnlyWhenNoSlackChannel(): void
    {
        // mobile / backend-js categories have a ClickUp channel but no Slack one.
        $messages = new SentryChatMessageFactory()->create(
            $this->event(),
            new RoutingDecision('mobile', 'production', null, 'CH_CLICKUP'),
        );

        self::assertCount(1, $messages);
        self::assertSame('clickup', $messages[0]->getTransport());
    }

    private function event(): SentryEvent
    {
        return new SentryEvent('4511585443381328', 'php', 'production', '42', 'Boom', 'App\\Foo::bar', 'error', 'https://sentry.example/issues/42');
    }
}
