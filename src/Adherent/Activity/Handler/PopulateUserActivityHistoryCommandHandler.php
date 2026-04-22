<?php

declare(strict_types=1);

namespace App\Adherent\Activity\Handler;

use App\Adherent\Activity\Command\PopulateUserActivityHistoryCommand;
use App\Adherent\Activity\SourceTypeEnum;
use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
readonly class PopulateUserActivityHistoryCommandHandler
{
    private const int BATCH_SIZE = 10_000;

    private const array EXCLUDED_ACTION_HISTORY_TYPES = [
        UserActionHistoryTypeEnum::IMPERSONATION_START->value,
        UserActionHistoryTypeEnum::IMPERSONATION_END->value,
        UserActionHistoryTypeEnum::SENSITIVE_DATA_ACCESS->value,
    ];

    public function __construct(
        private Connection $connection,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(PopulateUserActivityHistoryCommand $command): void
    {
        $nextLastId = match ($command->sourceType) {
            SourceTypeEnum::ActionHistory => $this->populateFromActionHistory($command->lastId),
            SourceTypeEnum::Hit => $this->populateFromHits($command->lastId),
        };

        if (null !== $nextLastId) {
            $this->bus->dispatch(new PopulateUserActivityHistoryCommand($command->sourceType, $nextLastId));
        } elseif (SourceTypeEnum::ActionHistory === $command->sourceType) {
            $this->bus->dispatch(new PopulateUserActivityHistoryCommand(SourceTypeEnum::Hit));
        }
    }

    private function populateFromActionHistory(int $lastId): ?int
    {
        ['max_id' => $maxId, 'total' => $total] = $this->connection->fetchAssociative(
            \sprintf(
                'SELECT COALESCE(MAX(sub.id), 0) AS max_id, COUNT(*) AS total
                 FROM (SELECT id FROM user_action_history WHERE id > :lastId AND type NOT IN (:excludedTypes) ORDER BY id LIMIT %d) sub',
                self::BATCH_SIZE,
            ),
            ['lastId' => $lastId, 'excludedTypes' => self::EXCLUDED_ACTION_HISTORY_TYPES],
            ['excludedTypes' => Connection::PARAM_STR_ARRAY],
        );

        if (0 === (int) $maxId) {
            return null;
        }

        $this->connection->executeStatement(
            <<<'SQL'
                    INSERT IGNORE INTO app_user_activity_history (uuid, adherent_id, source_type, source_id, event_type, occurred_at, metadata, created_at)
                    SELECT UUID(), h.adherent_id, :sourceType, h.id, h.type, h.date, h.data, NOW()
                    FROM user_action_history h
                    WHERE h.id > :lastId AND h.id <= :maxId AND h.type NOT IN (:excludedTypes)
                SQL,
            [
                'sourceType' => SourceTypeEnum::ActionHistory->value,
                'lastId' => $lastId,
                'maxId' => (int) $maxId,
                'excludedTypes' => self::EXCLUDED_ACTION_HISTORY_TYPES,
            ],
            ['excludedTypes' => Connection::PARAM_STR_ARRAY],
        );

        return self::BATCH_SIZE === (int) $total ? (int) $maxId : null;
    }

    private function populateFromHits(int $lastId): ?int
    {
        $eventTypes = [
            EventTypeEnum::Open->value,
            EventTypeEnum::Click->value,
            EventTypeEnum::ActivitySession->value,
        ];

        ['max_id' => $maxId, 'total' => $total] = $this->connection->fetchAssociative(
            \sprintf(
                'SELECT COALESCE(MAX(sub.id), 0) AS max_id, COUNT(*) AS total
                 FROM (SELECT id FROM app_hit WHERE id > :lastId AND event_type IN (:eventTypes) AND adherent_id IS NOT NULL ORDER BY id LIMIT %d) sub',
                self::BATCH_SIZE,
            ),
            ['lastId' => $lastId, 'eventTypes' => $eventTypes],
            ['eventTypes' => Connection::PARAM_STR_ARRAY],
        );

        if (0 === (int) $maxId) {
            return null;
        }

        $this->connection->executeStatement(
            <<<'SQL'
                    INSERT IGNORE INTO app_user_activity_history (uuid, adherent_id, source_type, source_id, event_type, occurred_at, metadata, created_at)
                    SELECT
                        UUID(),
                        h.adherent_id,
                        :sourceType,
                        h.id,
                        h.event_type,
                        h.app_date,
                        JSON_OBJECT(
                            'source', h.source,
                            'object_type', h.object_type,
                            'object_id', h.object_id,
                            'button_name', h.button_name,
                            'target_url', h.target_url
                        ),
                        NOW()
                    FROM app_hit h
                    WHERE h.event_type IN (:eventTypes)
                      AND h.adherent_id IS NOT NULL
                      AND h.id > :lastId
                      AND h.id <= :maxId
                SQL,
            [
                'sourceType' => SourceTypeEnum::Hit->value,
                'eventTypes' => $eventTypes,
                'lastId' => $lastId,
                'maxId' => (int) $maxId,
            ],
            ['eventTypes' => Connection::PARAM_STR_ARRAY],
        );

        return self::BATCH_SIZE === (int) $total ? (int) $maxId : null;
    }
}
