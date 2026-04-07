<?php

declare(strict_types=1);

namespace Tests\App\Unit\Firebase;

use App\Firebase\PushTokenStatusManager;
use App\Firebase\PushTokenUnsubscribeReasonEnum;
use App\Repository\PushTokenRepository;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PushTokenStatusManagerTest extends TestCase
{
    private PushTokenRepository&MockObject $pushTokenRepository;
    private PushTokenStatusManager $manager;

    protected function setUp(): void
    {
        $this->pushTokenRepository = $this->createMock(PushTokenRepository::class);
        $this->manager = new PushTokenStatusManager($this->pushTokenRepository);
    }

    public function testProcessReportWithUnknownTokensMarksAsTokenUnknown(): void
    {
        $report = MulticastSendReport::withItems([
            SendReport::failure(
                MessageTarget::with(MessageTarget::TOKEN, 'dead-token-1'),
                NotFound::becauseTokenNotFound('dead-token-1')
            ),
            SendReport::failure(
                MessageTarget::with(MessageTarget::TOKEN, 'dead-token-2'),
                NotFound::becauseTokenNotFound('dead-token-2')
            ),
        ]);

        $this->pushTokenRepository
            ->expects(self::once())
            ->method('markAsUnsubscribed')
            ->with(['dead-token-1', 'dead-token-2'], PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN)
        ;

        $this->pushTokenRepository
            ->expects(self::never())
            ->method('updateLastNotificationAt')
        ;

        $this->manager->processReport($report);
    }

    public function testProcessReportWithSuccessTokensUpdatesLastNotification(): void
    {
        $report = MulticastSendReport::withItems([
            SendReport::success(
                MessageTarget::with(MessageTarget::TOKEN, 'ok-token-1'),
                ['name' => 'projects/test/messages/123']
            ),
            SendReport::success(
                MessageTarget::with(MessageTarget::TOKEN, 'ok-token-2'),
                ['name' => 'projects/test/messages/456']
            ),
        ]);

        $this->pushTokenRepository
            ->expects(self::never())
            ->method('markAsUnsubscribed')
        ;

        $this->pushTokenRepository
            ->expects(self::once())
            ->method('updateLastNotificationAt')
            ->with(['ok-token-1', 'ok-token-2'])
        ;

        $this->manager->processReport($report);
    }

    public function testProcessReportWithMixedResultsProcessesBoth(): void
    {
        $report = MulticastSendReport::withItems([
            SendReport::success(
                MessageTarget::with(MessageTarget::TOKEN, 'ok-token'),
                ['name' => 'projects/test/messages/123']
            ),
            SendReport::failure(
                MessageTarget::with(MessageTarget::TOKEN, 'dead-token'),
                NotFound::becauseTokenNotFound('dead-token')
            ),
        ]);

        $this->pushTokenRepository
            ->expects(self::once())
            ->method('markAsUnsubscribed')
            ->with(['dead-token'], PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN)
        ;

        $this->pushTokenRepository
            ->expects(self::once())
            ->method('updateLastNotificationAt')
            ->with(['ok-token'])
        ;

        $this->manager->processReport($report);
    }

    public function testProcessReportWithEmptyReportDoesNothing(): void
    {
        $report = MulticastSendReport::withItems([]);

        $this->pushTokenRepository
            ->expects(self::never())
            ->method('markAsUnsubscribed')
        ;

        $this->pushTokenRepository
            ->expects(self::never())
            ->method('updateLastNotificationAt')
        ;

        $this->manager->processReport($report);
    }
}
