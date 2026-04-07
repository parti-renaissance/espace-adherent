<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Event\Event;
use App\Entity\PushToken;
use App\Firebase\PushTokenStatusManager;
use App\Firebase\PushTokenUnsubscribeReasonEnum;
use App\JeMengage\Push\Command\EventLiveBeginNotificationCommand;
use App\JeMengage\Push\Notification\EventLiveBeginNotification;
use App\JeMengage\Push\NotificationFactory;
use App\JeMengage\Push\TokenProvider\LiveBeginTokenProvider;
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

    // --- LiveBeginTokenProvider tests ---

    public function testLiveBeginTokenProviderSupportsEventLiveBeginNotification(): void
    {
        $provider = $this->get(LiveBeginTokenProvider::class);
        $notification = new EventLiveBeginNotification('title', 'body');
        $object = $this->createMock(\App\Entity\NotificationObjectInterface::class);

        self::assertTrue($provider->supports($notification, $object));
    }

    public function testLiveBeginTokenProviderDoesNotSupportOtherNotifications(): void
    {
        $provider = $this->get(LiveBeginTokenProvider::class);
        $notification = $this->createMock(\App\Firebase\Notification\NotificationInterface::class);
        $object = $this->createMock(\App\Entity\NotificationObjectInterface::class);

        self::assertFalse($provider->supports($notification, $object));
    }

    // --- LiveBeginTokenProvider returns tokens ---

    public function testLiveBeginTokenProviderReturnsScopeNational(): void
    {
        $provider = $this->get(LiveBeginTokenProvider::class);
        $notification = new EventLiveBeginNotification('title', 'body');
        $object = $this->createMock(\App\Entity\NotificationObjectInterface::class);
        $command = new EventLiveBeginNotificationCommand(Uuid::uuid4());

        $provider->getTokens($notification, $object, $command);

        self::assertSame('national', $notification->getScope());
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
