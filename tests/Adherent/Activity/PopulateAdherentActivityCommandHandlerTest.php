<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity;

use App\Adherent\Activity\AdherentActivityDescriptionBuilder;
use App\Adherent\Activity\BatchResult;
use App\Adherent\Activity\PopulateAdherentActivityCommand;
use App\Adherent\Activity\PopulateAdherentActivityCommandHandler;
use App\Adherent\Activity\PopulateAdherentActivityService;
use App\Adherent\Activity\SourceTypeEnum;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Adherent\Activity\AdherentActivity;
use App\Entity\AppHit;
use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\Adherent\Activity\AdherentActivityRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class PopulateAdherentActivityCommandHandlerTest extends AbstractKernelTestCase
{
    private PopulateAdherentActivityCommandHandler $handler;
    private AdherentActivityRepository $activityRepository;
    private MessageBusInterface&MockObject $bus;
    private LoggerInterface&MockObject $logger;
    private Adherent $adherent;

    public function testInsertsAllowedActionHistoryRows(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS);
        $this->createActionHistory(UserActionHistoryTypeEnum::PROFILE_UPDATE, ['first_name']);
        $this->createActionHistory(UserActionHistoryTypeEnum::IMPERSONATION_START);

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));

        // Then
        $activities = $this->activityRepository->findBy(['sourceType' => SourceTypeEnum::ActionHistory]);
        self::assertCount(2, $activities);
    }

    public function testSkipsNonAllowedActionHistoryTypes(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::IMPERSONATION_START);
        $this->createActionHistory(UserActionHistoryTypeEnum::IMPERSONATION_END);
        $this->createActionHistory(UserActionHistoryTypeEnum::SENSITIVE_DATA_ACCESS);

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));

        // Then
        self::assertSame(0, $this->activityRepository->count(['sourceType' => SourceTypeEnum::ActionHistory]));
    }

    public function testMapsActionHistoryFields(): void
    {
        // Given
        $date = new \DateTime('2024-01-15 10:00:00');
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS, ['ip' => '127.0.0.1'], $date);

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));

        // Then
        $activity = $this->activityRepository->findOneBy(['sourceType' => SourceTypeEnum::ActionHistory]);
        self::assertNotNull($activity);
        self::assertSame($this->adherent->getId(), $activity->adherent->getId());
        self::assertSame(UserActionHistoryTypeEnum::LOGIN_SUCCESS->value, $activity->eventType);
        self::assertSame('Connexion réussie', $activity->eventLabel);
        self::assertNull($activity->description); // login_success has no description by design
        self::assertEquals($date, $activity->occurredAt);
        self::assertSame(['ip' => '127.0.0.1'], $activity->metadata);
    }

    public function testStoresDescriptionForDelegatedAccessAdd(): void
    {
        // Given
        $this->createActionHistory(
            UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD,
            [
                'actor_name' => 'Victorio Fortest',
                'role' => 'Responsable de communication',
                'zones' => ['Hauts-de-Seine (92)'],
            ],
        );

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));

        // Then
        $activity = $this->activityRepository->findOneBy(['sourceType' => SourceTypeEnum::ActionHistory]);
        self::assertNotNull($activity);
        self::assertSame("Création d'accès délégué", $activity->eventLabel);
        self::assertSame('Victorio Fortest a ouvert un accès "Responsable de communication" sur Hauts-de-Seine (92)', $activity->description);
    }

    public function testFiltersHitsByEventType(): void
    {
        // Given
        $this->createHit(EventTypeEnum::Open);
        $this->createHit(EventTypeEnum::Impression);

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));

        // Then
        self::assertSame(1, $this->activityRepository->count(['sourceType' => SourceTypeEnum::Hit]));
        self::assertSame(0, $this->activityRepository->count([
            'sourceType' => SourceTypeEnum::Hit,
            'eventType' => EventTypeEnum::Impression->value,
        ]));
    }

    public function testMapsHitFieldsToMetadata(): void
    {
        // Given
        $date = new \DateTime('2024-01-15 10:00:00');
        $hit = $this->createHit(
            eventType: EventTypeEnum::Open,
            appDate: $date,
            objectType: TargetTypeEnum::Event,
            objectId: 'event-uuid-123',
            source: 'page_events',
            buttonName: 'cta_register',
            targetUrl: '/evenements/event-uuid-123',
        );

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));

        // Then
        $activity = $this->activityRepository->findOneBy(['sourceType' => SourceTypeEnum::Hit, 'sourceId' => $hit->getId()]);
        self::assertNotNull($activity);
        self::assertSame($this->adherent->getId(), $activity->adherent->getId());
        self::assertSame(EventTypeEnum::Open->value, $activity->eventType);
        self::assertSame('Ouverture', $activity->eventLabel);
        self::assertEquals($date, $activity->occurredAt);
        self::assertSame('page_events', $activity->metadata['source']);
        self::assertSame(TargetTypeEnum::Event->value, $activity->metadata['object_type']);
        self::assertSame('event-uuid-123', $activity->metadata['object_id']);
        self::assertSame('cta_register', $activity->metadata['button_name']);
        self::assertSame('/evenements/event-uuid-123', $activity->metadata['target_url']);
    }

    public function testIsIdempotent(): void
    {
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS);
        $this->createHit(EventTypeEnum::Click);

        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));
        $countAfterFirst = $this->activityRepository->count(['adherent' => $this->adherent]);

        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));
        $countAfterSecond = $this->activityRepository->count(['adherent' => $this->adherent]);

        self::assertGreaterThan(0, $countAfterFirst);
        self::assertSame($countAfterFirst, $countAfterSecond);
    }

    public function testDispatchesHitCommandAfterActionHistoryIsFullyProcessed(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS);

        $this->bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(fn ($msg) => $msg instanceof PopulateAdherentActivityCommand && SourceTypeEnum::Hit === $msg->sourceType))
            ->willReturn(new Envelope(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit)));

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));
    }

    public function testSkipsActionHistoryRowsCreatedWithinLastMinute(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS, null, new \DateTime('-30 seconds'));
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_FAILURE, null, new \DateTime('-2 minutes'));

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));

        // Then
        $activities = $this->activityRepository->findBy(['sourceType' => SourceTypeEnum::ActionHistory]);
        self::assertCount(1, $activities);
        self::assertSame(UserActionHistoryTypeEnum::LOGIN_FAILURE->value, $activities[0]->eventType);
    }

    public function testSkipsHitRowsCreatedWithinLastMinute(): void
    {
        // Given
        $this->createHit(EventTypeEnum::Open, new \DateTime('-30 seconds'));
        $this->createHit(EventTypeEnum::Click, new \DateTime('-2 minutes'));

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::Hit));

        // Then
        $activities = $this->activityRepository->findBy(['sourceType' => SourceTypeEnum::Hit]);
        self::assertCount(1, $activities);
        self::assertSame(EventTypeEnum::Click->value, $activities[0]->eventType);
    }

    public function testLogsInfoWithStructuredContextAfterBatch(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS);

        $this->logger
            ->expects(self::atLeastOnce())
            ->method('info')
            ->with(
                'AdherentActivity batch finished',
                self::callback(static function (array $context): bool {
                    return 'action_history' === $context['source_type']
                        && $context['inserted'] >= 1
                        && \array_key_exists('last_id_before', $context)
                        && \array_key_exists('last_id_after', $context)
                        && \array_key_exists('duration_ms', $context);
                }),
            );

        // When
        ($this->handler)(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));
    }

    public function testLogsWarningWhenBatchEmptyButSourceHasEligibleRow(): void
    {
        // Given — service mock simulates a stalled pipeline scenario
        $serviceMock = $this->createMock(PopulateAdherentActivityService::class);
        $serviceMock
            ->expects(self::once())
            ->method('processBatch')
            ->with(SourceTypeEnum::ActionHistory)
            ->willReturn(new BatchResult(inserted: 0, lastIdBefore: 100, lastIdAfter: 100));
        $serviceMock
            ->expects(self::once())
            ->method('findNextEligibleId')
            ->with(SourceTypeEnum::ActionHistory)
            ->willReturn(123);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('warning')
            ->with(
                self::stringContains('stalled'),
                self::callback(static fn (array $context): bool => 123 === $context['next_eligible_id']),
            );
        $logger->expects(self::never())->method('info');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $handler = new PopulateAdherentActivityCommandHandler($serviceMock, $bus, $logger);

        // When
        $handler(new PopulateAdherentActivityCommand(SourceTypeEnum::ActionHistory));
    }

    public function testFindNextEligibleIdReturnsNullWhenSourceEmpty(): void
    {
        $service = new PopulateAdherentActivityService($this->get(Connection::class), new AdherentActivityDescriptionBuilder());

        self::assertNull($service->findNextEligibleId(SourceTypeEnum::ActionHistory));
    }

    public function testFindNextEligibleIdReturnsMinIdWhenEligibleRowsExist(): void
    {
        // Given
        $this->createActionHistory(UserActionHistoryTypeEnum::LOGIN_SUCCESS, null, new \DateTime('-3 minutes'));
        $this->createActionHistory(UserActionHistoryTypeEnum::PROFILE_UPDATE, ['x'], new \DateTime('-2 minutes'));

        // When
        $service = new PopulateAdherentActivityService($this->get(Connection::class), new AdherentActivityDescriptionBuilder());
        $nextId = $service->findNextEligibleId(SourceTypeEnum::ActionHistory);

        // Then
        $minId = (int) $this->get(Connection::class)->fetchOne('SELECT MIN(id) FROM user_action_history');
        self::assertSame($minId, $nextId);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $connection = $this->get(Connection::class);
        $connection->executeStatement('DELETE FROM adherent_activity');
        $connection->executeStatement('DELETE FROM user_action_history');
        $connection->executeStatement('DELETE FROM app_hit');

        $this->adherent = $this->manager->getRepository(Adherent::class)
            ->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);

        $this->activityRepository = $this->manager->getRepository(AdherentActivity::class);

        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->bus->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new PopulateAdherentActivityCommandHandler(
            new PopulateAdherentActivityService($connection, new AdherentActivityDescriptionBuilder()),
            $this->bus,
            $this->logger,
        );
    }

    private function createActionHistory(
        UserActionHistoryTypeEnum $type,
        ?array $data = null,
        ?\DateTimeInterface $date = null,
    ): void {
        $history = new UserActionHistory($this->adherent, $type, $date ?? new \DateTime('-5 minutes'), $data);
        $this->manager->persist($history);
        $this->manager->flush();
    }

    private function createHit(
        EventTypeEnum $eventType,
        ?\DateTimeInterface $appDate = null,
        ?TargetTypeEnum $objectType = null,
        ?string $objectId = null,
        ?string $source = null,
        ?string $buttonName = null,
        ?string $targetUrl = null,
    ): AppHit {
        $hit = new AppHit();
        $hit->eventType = $eventType;
        $hit->adherent = $this->adherent;
        $hit->activitySessionUuid = Uuid::v4();
        $hit->appDate = $appDate ?? new \DateTime('-5 minutes');
        $hit->objectType = $objectType;
        $hit->objectId = $objectId;
        $hit->source = $source;
        $hit->buttonName = $buttonName;
        $hit->targetUrl = $targetUrl;

        $this->manager->persist($hit);
        $this->manager->flush();

        // Align created_at (set to NOW by EntityTimestampableTrait) with appDate, so the
        // pipeline buffer (filtering on created_at) treats fixture rows as past events.
        $this->get(Connection::class)->executeStatement(
            'UPDATE app_hit SET created_at = ? WHERE id = ?',
            [$hit->appDate->format('Y-m-d H:i:s'), $hit->getId()],
        );

        return $hit;
    }
}
