<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Event\Event;
use App\Entity\PushNotification;
use App\Entity\PushToken;
use App\Firebase\PushNotificationStatusEnum;
use App\Firebase\PushTokenStatusManager;
use App\Firebase\PushTokenUnsubscribeReasonEnum;
use App\JeMengage\Push\Command\EventLiveBeginNotificationCommand;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;
use App\JeMengage\Push\NotificationFactory;
use App\Repository\PushTokenRepository;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\SendReport;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
final class PushNotificationFunctionalTest extends AbstractKernelTestCase
{
    private ?PushTokenRepository $pushTokenRepository = null;
    private ?PushTokenStatusManager $pushTokenStatusManager = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pushTokenRepository = $this->getRepository(PushToken::class);
        $this->pushTokenStatusManager = $this->get(PushTokenStatusManager::class);
    }

    protected function tearDown(): void
    {
        $this->pushTokenRepository = null;
        $this->pushTokenStatusManager = null;

        parent::tearDown();
    }

    // --- PushTokenStatusManager tests ---

    public function testProcessReportWithDeadTokensMarksAsUnsubscribedInDb(): void
    {
        $token = $this->createAndPersistToken('func-test-dead-'.uniqid());

        self::assertNull($token->unsubscribedAt);

        $report = MulticastSendReport::withItems([
            SendReport::failure(
                MessageTarget::with(MessageTarget::TOKEN, $token->identifier),
                NotFound::becauseTokenNotFound($token->identifier)
            ),
        ]);

        $this->pushTokenStatusManager->processReport($report);

        $this->manager->clear();
        $tokenAfter = $this->pushTokenRepository->findByIdentifier($token->identifier);

        self::assertNotNull($tokenAfter, 'Token should still exist in DB');
        self::assertNotNull($tokenAfter->unsubscribedAt, 'Token should be marked as unsubscribed after FCM reports it as unknown');
        self::assertSame(PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN, $tokenAfter->unsubscribedReason, 'Reason should be token_unknown');
    }

    public function testProcessReportWithSuccessTokensUpdatesLastNotificationAtInDb(): void
    {
        $token = $this->createAndPersistToken('func-test-success-'.uniqid());

        self::assertNull($token->lastNotificationAt);

        $report = MulticastSendReport::withItems([
            SendReport::success(
                MessageTarget::with(MessageTarget::TOKEN, $token->identifier),
                ['name' => 'projects/test/messages/123']
            ),
        ]);

        $this->pushTokenStatusManager->processReport($report);

        $this->manager->clear();
        $tokenAfter = $this->pushTokenRepository->findByIdentifier($token->identifier);

        self::assertNotNull($tokenAfter, 'Token should still exist in DB');
        self::assertNotNull($tokenAfter->lastNotificationAt, 'lastNotificationAt should be set after successful delivery');
        self::assertTrue($tokenAfter->lastNotificationSuccess, 'lastNotificationSuccess should be true');
    }

    public function testMarkAsUnsubscribedDoesNotOverwriteExistingUnsubscribedAt(): void
    {
        $token = $this->createAndPersistToken('func-test-already-dead-'.uniqid());
        $token->unsubscribedAt = new \DateTime('-7 days');
        $this->manager->flush();

        $originalDate = $token->unsubscribedAt;

        $this->pushTokenRepository->markAsUnsubscribed([$token->identifier], PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN);

        $this->manager->clear();
        $tokenAfter = $this->pushTokenRepository->findByIdentifier($token->identifier);

        self::assertNotNull($tokenAfter, 'Token should still exist in DB');
        self::assertEquals(
            $originalDate->format('Y-m-d'),
            $tokenAfter->unsubscribedAt->format('Y-m-d'),
            'unsubscribedAt should not be overwritten if already set'
        );
    }

    public function testMarkAsUnsubscribedThenFindByIdentifierTokenStillExists(): void
    {
        $token = $this->createAndPersistToken('func-test-still-exists-'.uniqid());

        $this->pushTokenRepository->markAsUnsubscribed([$token->identifier], PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN);

        $this->manager->clear();
        $tokenAfter = $this->pushTokenRepository->findByIdentifier($token->identifier);

        self::assertNotNull($tokenAfter, 'Token should exist in DB after being marked as unsubscribed');
        self::assertNotNull($tokenAfter->unsubscribedAt, 'Token should have unsubscribedAt set');
    }

    // --- NotificationFactory tests ---

    public function testNotificationFactoryCreatesEventLiveBeginNotification(): void
    {
        $factory = $this->get(NotificationFactory::class);
        $event = $this->createMock(Event::class);
        $event->method('getName')->willReturn('Test Live Event');

        $command = new EventLiveBeginNotificationCommand(Uuid::uuid4());
        $notification = $factory->create($event, $command);

        self::assertInstanceOf(EventLiveBeginNotification::class, $notification);
        self::assertSame('🔴 On est en direct !', $notification->getTitle());
        self::assertSame('Test Live Event', $notification->getBody());
    }

    // --- PushNotification entity tests ---

    public function testPushNotificationCreatedWithCorrectChunksTotal(): void
    {
        $pushNotification = new PushNotification(
            'TestNotification',
            'Title',
            'Body',
            'test_scope',
            ['key' => 'value'],
            5,
        );

        $this->manager->persist($pushNotification);
        $this->manager->flush();
        $this->manager->clear();

        $loaded = $this->manager->getRepository(PushNotification::class)->findOneBy(['uuid' => $pushNotification->getUuid()]);

        self::assertNotNull($loaded);
        self::assertSame(5, $loaded->chunksTotal);
        self::assertSame(0, $loaded->chunksDelivered);
        self::assertSame(PushNotificationStatusEnum::PENDING, $loaded->status);
    }

    public function testPushNotificationStatusTransitions(): void
    {
        $pushNotification = new PushNotification(
            'TestNotification',
            'Title',
            'Body',
            null,
            null,
            3,
        );

        self::assertSame(PushNotificationStatusEnum::PENDING, $pushNotification->status);

        $pushNotification->recordChunkResult(300, 280, 20);
        self::assertSame(PushNotificationStatusEnum::PARTIAL, $pushNotification->status);
        self::assertSame(300, $pushNotification->totalTokens);
        self::assertSame(280, $pushNotification->totalSuccess);
        self::assertSame(20, $pushNotification->totalFailed);
        self::assertSame(1, $pushNotification->chunksDelivered);

        $pushNotification->recordChunkResult(300, 300, 0);
        self::assertSame(PushNotificationStatusEnum::PARTIAL, $pushNotification->status);
        self::assertSame(2, $pushNotification->chunksDelivered);

        $pushNotification->recordChunkResult(150, 150, 0);
        self::assertSame(PushNotificationStatusEnum::DELIVERED, $pushNotification->status);
        self::assertSame(3, $pushNotification->chunksDelivered);
        self::assertSame(750, $pushNotification->totalTokens);
        self::assertSame(730, $pushNotification->totalSuccess);
        self::assertSame(20, $pushNotification->totalFailed);
    }

    // --- AudienceFilter scope targets test ---

    public function testFindAllForAdherentMessageRunsWithoutError(): void
    {
        $message = $this->createMock(\App\Entity\AdherentMessage\AdherentMessage::class);
        $filter = new \App\Entity\AdherentMessage\AdherentMessageFilter();
        $message->method('getFilter')->willReturn($filter);

        $result = $this->pushTokenRepository->findAllForAdherentMessage($message);

        self::assertIsArray($result);
    }

    // --- helpers ---

    private function createAndPersistToken(string $identifier): PushToken
    {
        $token = new PushToken(Uuid::uuid4(), $identifier);
        $this->manager->persist($token);
        $this->manager->flush();

        return $token;
    }
}
