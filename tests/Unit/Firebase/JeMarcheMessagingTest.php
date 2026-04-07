<?php

declare(strict_types=1);

namespace Tests\App\Unit\Firebase;

use App\Entity\Notification as NotificationEntity;
use App\Firebase\Event\PushNotificationSentEvent;
use App\Firebase\JeMarcheMessaging;
use App\Firebase\Notification\PushChunkNotification;
use App\Firebase\PushTokenStatusManager;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Contract\Messaging as BaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class JeMarcheMessagingTest extends TestCase
{
    private BaseMessaging&MockObject $firebaseMessaging;
    private EntityManagerInterface&MockObject $entityManager;
    private NotificationRepository&MockObject $notificationRepository;
    private EventDispatcherInterface&MockObject $eventDispatcher;
    private PushTokenStatusManager&MockObject $pushTokenStatusManager;
    private JeMarcheMessaging $messaging;

    protected function setUp(): void
    {
        $this->firebaseMessaging = $this->createMock(BaseMessaging::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->notificationRepository = $this->createMock(NotificationRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->pushTokenStatusManager = $this->createMock(PushTokenStatusManager::class);

        $this->messaging = new JeMarcheMessaging(
            $this->firebaseMessaging,
            $this->entityManager,
            $this->notificationRepository,
            $this->eventDispatcher,
            $this->pushTokenStatusManager,
        );
    }

    public function testSendWithSuccessfulMulticastProcessesReportAndDelivers(): void
    {
        $notification = $this->createNotification(['token-1', 'token-2']);
        $report = MulticastSendReport::withItems([]);

        $this->notificationRepository
            ->expects(self::once())
            ->method('keyExists')
            ->with(self::isType('string'))
            ->willReturn(false)
        ;

        $this->firebaseMessaging
            ->expects(self::once())
            ->method('sendMulticast')
            ->with(self::isInstanceOf(CloudMessage::class), ['token-1', 'token-2'])
            ->willReturn($report)
        ;

        $this->pushTokenStatusManager
            ->expects(self::once())
            ->method('processReport')
            ->with($report)
        ;

        $this->entityManager->expects(self::exactly(2))->method('flush');

        $this->eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PushNotificationSentEvent::class))
        ;

        $this->messaging->send($notification);
    }

    public function testSendWithPartialFailuresStillProcessesReport(): void
    {
        $notification = $this->createNotification(['token-ok', 'token-dead']);
        $report = MulticastSendReport::withItems([]);

        $this->notificationRepository
            ->method('keyExists')
            ->with(self::isType('string'))
            ->willReturn(false)
        ;

        $this->firebaseMessaging
            ->expects(self::once())
            ->method('sendMulticast')
            ->with(self::isInstanceOf(CloudMessage::class), ['token-ok', 'token-dead'])
            ->willReturn($report)
        ;

        $this->pushTokenStatusManager
            ->expects(self::once())
            ->method('processReport')
            ->with($report)
        ;

        $this->messaging->send($notification);
    }

    public function testSendWithExceptionRemovesEntityAndRethrows(): void
    {
        $notification = $this->createNotification(['token-1']);
        $exception = new \RuntimeException('FCM unavailable');

        $this->notificationRepository
            ->method('keyExists')
            ->with(self::isType('string'))
            ->willReturn(false)
        ;

        $this->firebaseMessaging
            ->expects(self::once())
            ->method('sendMulticast')
            ->with(self::isInstanceOf(CloudMessage::class), ['token-1'])
            ->willThrowException($exception)
        ;

        $this->pushTokenStatusManager
            ->expects(self::never())
            ->method('processReport')
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('remove')
            ->with(self::isInstanceOf(NotificationEntity::class))
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FCM unavailable');

        $this->messaging->send($notification);
    }

    public function testSendWithDuplicateKeySkipsChunk(): void
    {
        $notification = $this->createNotification(['token-1']);

        $this->notificationRepository
            ->expects(self::once())
            ->method('keyExists')
            ->with(self::isType('string'))
            ->willReturn(true)
        ;

        $this->firebaseMessaging
            ->expects(self::never())
            ->method('sendMulticast')
        ;

        $this->pushTokenStatusManager
            ->expects(self::never())
            ->method('processReport')
        ;

        $this->messaging->send($notification);
    }

    public function testSendWithMultipleChunksSendsEachChunk(): void
    {
        $tokens = array_map(
            function (int $i): string { return 'token-'.$i; },
            range(1, 600)
        );
        $notification = $this->createNotification($tokens);

        $report = MulticastSendReport::withItems([]);

        $this->notificationRepository
            ->method('keyExists')
            ->with(self::isType('string'))
            ->willReturn(false)
        ;

        $this->firebaseMessaging
            ->expects(self::exactly(2))
            ->method('sendMulticast')
            ->with(self::isInstanceOf(CloudMessage::class), self::isType('array'))
            ->willReturn($report)
        ;

        $this->pushTokenStatusManager
            ->expects(self::exactly(2))
            ->method('processReport')
            ->with($report)
        ;

        $this->entityManager->expects(self::exactly(4))->method('flush');

        $this->messaging->send($notification);
    }

    private function createNotification(array $tokens): PushChunkNotification
    {
        $notification = new PushChunkNotification(
            'Test Title',
            'Test Body',
            ['link' => '/test'],
            'test_scope',
            'TestNotification',
        );
        $notification->setTokens($tokens);

        return $notification;
    }
}
