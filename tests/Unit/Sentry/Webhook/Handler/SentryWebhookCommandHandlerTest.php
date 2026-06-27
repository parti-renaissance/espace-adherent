<?php

declare(strict_types=1);

namespace Tests\App\Unit\Sentry\Webhook\Handler;

use App\Sentry\Webhook\Command\SentryWebhookCommand;
use App\Sentry\Webhook\Handler\SentryWebhookCommandHandler;
use App\Sentry\Webhook\Notifier\SentryChatMessageFactory;
use App\Sentry\Webhook\Routing\SentryEventRouter;
use App\Sentry\Webhook\SentryEvent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

#[Group('unit')]
class SentryWebhookCommandHandlerTest extends TestCase
{
    public function testHandleFansOutAndContinuesWhenADestinationThrows(): void
    {
        $sentTransports = [];

        $chatter = $this->createMock(ChatterInterface::class);
        $chatter
            ->expects(self::exactly(2))
            ->method('send')
            ->willReturnCallback(function (ChatMessage $message) use (&$sentTransports) {
                $sentTransports[] = $message->getTransport();

                // The first destination fails; it must NOT prevent the others.
                if (1 === \count($sentTransports)) {
                    throw new \RuntimeException('transport is down');
                }

                return null;
            })
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(self::stringContains('Destination failed'))
        ;

        $router = new SentryEventRouter(
            ['backend-php' => ['production' => ['slack' => 'C_BE_PHP_PRD', 'clickup' => 'CH_BE_PHP_PRD']]],
            '4511585504067664',
            '4511585443381328',
        );

        $handler = new SentryWebhookCommandHandler($router, new SentryChatMessageFactory(), $chatter, $logger);
        $handler(new SentryWebhookCommand(new SentryEvent('4511585443381328', 'php', 'production', 'issue-42', 'Boom', null, 'error', null)));

        self::assertSame(['slack', 'clickup'], $sentTransports);
    }
}
