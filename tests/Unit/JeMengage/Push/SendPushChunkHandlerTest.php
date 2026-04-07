<?php

declare(strict_types=1);

namespace Tests\App\Unit\JeMengage\Push;

use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\PushChunkNotification;
use App\JeMengage\Push\Command\SendPushChunkCommand;
use App\JeMengage\Push\SendPushChunkHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

final class SendPushChunkHandlerTest extends TestCase
{
    private JeMarcheMessaging&MockObject $messaging;
    private CacheInterface&MockObject $cache;
    private SendPushChunkHandler $handler;

    protected function setUp(): void
    {
        $this->messaging = $this->createMock(JeMarcheMessaging::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->handler = new SendPushChunkHandler($this->messaging, $this->cache);
    }

    public function testInvokeWithValidChunkSendsNotification(): void
    {
        $command = new SendPushChunkCommand(
            'EventCreationNotification',
            'Test Title',
            'Test Body',
            'test_scope',
            ['key' => 'value'],
            ['token-1', 'token-2'],
            'App\Entity\Event:uuid-123:push:0',
        );

        $this->cache
            ->expects(self::once())
            ->method('has')
            ->with('App\Entity\Event:uuid-123:push:0')
            ->willReturn(false)
        ;

        $this->messaging
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(function (PushChunkNotification $notification): bool {
                return 'Test Title' === $notification->getTitle()
                    && 'Test Body' === $notification->getBody()
                    && 'test_scope' === $notification->getScope()
                    && 'EventCreationNotification' === $notification->originalClassName
                    && ['token-1', 'token-2'] === $notification->getTokens()
                    && null === $notification->pushNotificationUuid;
            }))
        ;

        $this->cache
            ->expects(self::once())
            ->method('set')
            ->with('App\Entity\Event:uuid-123:push:0', true, 900)
        ;

        ($this->handler)($command);
    }

    public function testInvokeWithCacheHitSkipsSending(): void
    {
        $command = new SendPushChunkCommand(
            'TestNotification',
            'Title',
            'Body',
            null,
            [],
            ['token-1'],
            'test:key:push:0',
        );

        $this->cache
            ->expects(self::once())
            ->method('has')
            ->with('test:key:push:0')
            ->willReturn(true)
        ;

        $this->messaging->expects(self::never())->method('send');
        $this->cache->expects(self::never())->method('set');

        ($this->handler)($command);
    }

    public function testInvokeWithEmptyTokensSkipsSending(): void
    {
        $command = new SendPushChunkCommand(
            'TestNotification',
            'Title',
            'Body',
            null,
            [],
            [],
            'test:key:push:0',
        );

        $this->cache
            ->expects(self::once())
            ->method('has')
            ->with('test:key:push:0')
            ->willReturn(false)
        ;

        $this->messaging->expects(self::never())->method('send');
        $this->cache->expects(self::never())->method('set');

        ($this->handler)($command);
    }

    public function testInvokeSetsCorrectCacheTTL(): void
    {
        $command = new SendPushChunkCommand(
            'TestNotification',
            'Title',
            'Body',
            null,
            [],
            ['token-1'],
            'ttl:test:push:0',
        );

        $this->cache->method('has')->with('ttl:test:push:0')->willReturn(false);
        $this->messaging->method('send');

        $this->cache
            ->expects(self::once())
            ->method('set')
            ->with('ttl:test:push:0', true, 900)
        ;

        ($this->handler)($command);
    }

    public function testInvokeWithPushNotificationUuidPropagatesIt(): void
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4();
        $command = new SendPushChunkCommand(
            'TestNotification',
            'Title',
            'Body',
            null,
            [],
            ['token-1'],
            'uuid:test:push:0',
            $uuid,
        );

        $this->cache->method('has')->with('uuid:test:push:0')->willReturn(false);

        $this->messaging
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(function (PushChunkNotification $notification) use ($uuid): bool {
                return $uuid === $notification->pushNotificationUuid;
            }))
        ;

        $this->cache->method('set');

        ($this->handler)($command);
    }
}
