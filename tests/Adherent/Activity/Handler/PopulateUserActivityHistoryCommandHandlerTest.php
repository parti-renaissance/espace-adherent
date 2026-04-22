<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity\Handler;

use App\Adherent\Activity\Command\PopulateUserActivityHistoryCommand;
use App\Adherent\Activity\Handler\PopulateUserActivityHistoryCommandHandler;
use App\Adherent\Activity\SourceTypeEnum;
use App\Entity\Adherent;
use App\Entity\AppHit;
use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class PopulateUserActivityHistoryCommandHandlerTest extends AbstractKernelTestCase
{
    private PopulateUserActivityHistoryCommandHandler $handler;
    private Connection $connection;
    private MessageBusInterface&MockObject $bus;

    public function testInsertsActionHistoryRows(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $history = new UserActionHistory(
            $adherent,
            UserActionHistoryTypeEnum::LOGIN_SUCCESS,
            new \DateTime('2026-04-01 10:00:00'),
            ['ip' => '127.0.0.1'],
        );
        $this->manager->persist($history);
        $this->manager->flush();

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::ActionHistory));

        // Then
        $results = $this->connection->executeQuery(
            'SELECT * FROM app_user_activity_history WHERE source_type = :sourceType AND adherent_id = :adherentId',
            ['sourceType' => SourceTypeEnum::ActionHistory->value, 'adherentId' => $adherent->getId()],
        )->fetchAllAssociative();

        self::assertCount(1, $results);
        self::assertSame(SourceTypeEnum::ActionHistory->value, $results[0]['source_type']);
        self::assertSame(UserActionHistoryTypeEnum::LOGIN_SUCCESS->value, $results[0]['event_type']);
        self::assertSame($adherent->getId(), (int) $results[0]['adherent_id']);
        self::assertSame('2026-04-01 10:00:00', $results[0]['occurred_at']);
        self::assertNotNull($results[0]['metadata']);
        self::assertNotNull($results[0]['created_at']);
    }

    public function testFiltersHitsByEventType(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $hitOpen = $this->createAppHit($adherent, EventTypeEnum::Open, source: 'page_timeline', objectType: TargetTypeEnum::Event, objectId: 'abc-123');
        $hitClick = $this->createAppHit($adherent, EventTypeEnum::Click, buttonName: 'like');
        $hitSession = $this->createAppHit($adherent, EventTypeEnum::ActivitySession);
        $hitImpression = $this->createAppHit($adherent, EventTypeEnum::Impression);
        $this->manager->flush();

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit));

        // Then
        $allowedIds = [$hitOpen->getId(), $hitClick->getId(), $hitSession->getId()];
        $presentCount = (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM app_user_activity_history WHERE source_type = :sourceType AND source_id IN (:sourceIds)',
            ['sourceType' => SourceTypeEnum::Hit->value, 'sourceIds' => $allowedIds],
            ['sourceIds' => Connection::PARAM_INT_ARRAY],
        )->fetchOne();
        self::assertSame(3, $presentCount);

        $impressionCount = (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM app_user_activity_history WHERE source_type = :sourceType AND source_id = :sourceId',
            ['sourceType' => SourceTypeEnum::Hit->value, 'sourceId' => $hitImpression->getId()],
        )->fetchOne();
        self::assertSame(0, $impressionCount);
    }

    public function testMapsHitFieldsToMetadata(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $hit = $this->createAppHit($adherent, EventTypeEnum::Open, source: 'page_timeline', objectType: TargetTypeEnum::Event, objectId: 'abc-123');
        $this->manager->flush();

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit));

        // Then
        $row = $this->connection->executeQuery(
            'SELECT metadata FROM app_user_activity_history WHERE source_type = :sourceType AND source_id = :sourceId',
            ['sourceType' => SourceTypeEnum::Hit->value, 'sourceId' => $hit->getId()],
        )->fetchAssociative();

        $metadata = json_decode($row['metadata'], true);
        self::assertSame('page_timeline', $metadata['source']);
        self::assertSame('event', $metadata['object_type']);
        self::assertSame('abc-123', $metadata['object_id']);
    }

    public function testIsIdempotent(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $history = new UserActionHistory($adherent, UserActionHistoryTypeEnum::PROFILE_UPDATE, new \DateTime());
        $this->manager->persist($history);
        $this->createAppHit($adherent, EventTypeEnum::Click);
        $this->manager->flush();

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::ActionHistory));
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit));
        $countAfterFirst = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM app_user_activity_history');

        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::ActionHistory));
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit));
        $countAfterSecond = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM app_user_activity_history');

        // Then
        self::assertSame($countAfterFirst, $countAfterSecond);
    }

    public function testDispatchesHitCommandAfterActionHistory(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $history = new UserActionHistory($adherent, UserActionHistoryTypeEnum::LOGIN_SUCCESS, new \DateTime());
        $this->manager->persist($history);
        $this->manager->flush();

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(fn ($msg) => $msg instanceof PopulateUserActivityHistoryCommand && SourceTypeEnum::Hit === $msg->sourceType))
            ->willReturn(new Envelope(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit)));

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::ActionHistory));
    }

    public function testSkipsExcludedActionHistoryTypes(): void
    {
        // Given
        $adherent = $this->getAdherentRepository()->findOneByEmail('luciole1989@spambox.fr');
        $impersonationStart = new UserActionHistory($adherent, UserActionHistoryTypeEnum::IMPERSONATION_START, new \DateTime());
        $impersonationEnd = new UserActionHistory($adherent, UserActionHistoryTypeEnum::IMPERSONATION_END, new \DateTime());
        $sensitiveAccess = new UserActionHistory($adherent, UserActionHistoryTypeEnum::SENSITIVE_DATA_ACCESS, new \DateTime());
        $this->manager->persist($impersonationStart);
        $this->manager->persist($impersonationEnd);
        $this->manager->persist($sensitiveAccess);
        $this->manager->flush();

        $excludedIds = $this->connection->executeQuery(
            'SELECT id FROM user_action_history WHERE type IN (:types) AND adherent_id = :adherentId ORDER BY id DESC LIMIT 3',
            [
                'types' => [
                    UserActionHistoryTypeEnum::IMPERSONATION_START->value,
                    UserActionHistoryTypeEnum::IMPERSONATION_END->value,
                    UserActionHistoryTypeEnum::SENSITIVE_DATA_ACCESS->value,
                ],
                'adherentId' => $adherent->getId(),
            ],
            ['types' => Connection::PARAM_STR_ARRAY],
        )->fetchFirstColumn();

        // When
        ($this->handler)(new PopulateUserActivityHistoryCommand(SourceTypeEnum::ActionHistory));

        // Then
        $count = (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM app_user_activity_history WHERE source_type = :sourceType AND source_id IN (:sourceIds)',
            ['sourceType' => SourceTypeEnum::ActionHistory->value, 'sourceIds' => $excludedIds],
            ['sourceIds' => Connection::PARAM_INT_ARRAY],
        )->fetchOne();
        self::assertSame(0, $count);
    }

    private function createAppHit(
        Adherent $adherent,
        EventTypeEnum $eventType,
        ?string $source = null,
        ?TargetTypeEnum $objectType = null,
        ?string $objectId = null,
        ?string $buttonName = null,
    ): AppHit {
        $hit = new AppHit();
        $hit->adherent = $adherent;
        $hit->eventType = $eventType;
        $hit->appDate = new \DateTimeImmutable();
        $hit->activitySessionUuid = Uuid::uuid4();
        $hit->source = $source;
        $hit->objectType = $objectType;
        $hit->objectId = $objectId;
        $hit->buttonName = $buttonName;

        $this->manager->persist($hit);

        return $hit;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get(Connection::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));
        $this->handler = new PopulateUserActivityHistoryCommandHandler($this->connection, $this->bus);
    }
}
