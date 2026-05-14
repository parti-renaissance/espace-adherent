<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use App\JeMengage\Hit\TargetTypeEnum;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

class PopulateAdherentActivityService
{
    public const int BATCH_SIZE = 10_000;

    public function __construct(
        private readonly Connection $connection,
        private readonly AdherentActivityDescriptionBuilder $descriptionBuilder,
    ) {
    }

    public function processBatch(SourceTypeEnum $sourceType): BatchResult
    {
        $lastIdBefore = $this->fetchLastIngestedSourceId($sourceType);
        $threshold = new \DateTimeImmutable('-1 minute')->format('Y-m-d H:i:s');

        $rows = match ($sourceType) {
            SourceTypeEnum::ActionHistory => $this->fetchActionHistoryRows($lastIdBefore, $threshold),
            SourceTypeEnum::Hit => $this->fetchHitRows($lastIdBefore, $threshold),
        };

        if (empty($rows)) {
            return new BatchResult(0, $lastIdBefore, $lastIdBefore);
        }

        $enriched = array_map(
            fn (array $row): array => $this->enrich($row, $sourceType),
            $rows,
        );

        $this->bulkInsert($enriched);

        return new BatchResult(
            \count($enriched),
            $lastIdBefore,
            (int) max(array_column($rows, 'source_id')),
        );
    }

    public function findNextEligibleId(SourceTypeEnum $sourceType): ?int
    {
        $lastId = $this->fetchLastIngestedSourceId($sourceType);
        $threshold = new \DateTimeImmutable('-1 minute')->format('Y-m-d H:i:s');

        $result = match ($sourceType) {
            SourceTypeEnum::ActionHistory => $this->connection->fetchOne(
                <<<'SQL'
                    SELECT MIN(h.id) FROM user_action_history h
                    WHERE h.type IN (:allowedTypes)
                      AND h.id > :lastId
                      AND h.date < :threshold
                    SQL,
                ['allowedTypes' => AdherentActivityLabels::actionHistoryKeys(), 'lastId' => $lastId, 'threshold' => $threshold],
                ['allowedTypes' => ArrayParameterType::STRING],
            ),
            SourceTypeEnum::Hit => $this->connection->fetchOne(
                <<<'SQL'
                    SELECT MIN(h.id) FROM app_hit h
                    WHERE h.event_type IN (:eventTypes)
                      AND h.id > :lastId
                      AND h.created_at < :threshold
                    SQL,
                ['eventTypes' => AdherentActivityLabels::hitEventKeys(), 'lastId' => $lastId, 'threshold' => $threshold],
                ['eventTypes' => ArrayParameterType::STRING],
            ),
        };

        return null === $result || false === $result ? null : (int) $result;
    }

    private function fetchLastIngestedSourceId(SourceTypeEnum $sourceType): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COALESCE(MAX(source_id), 0) FROM adherent_activity WHERE source_type = :sourceType',
            ['sourceType' => $sourceType->value],
        );
    }

    /** @return list<array{source_id: int, adherent_id: int, event_type: string, occurred_at: string, metadata_json: ?string}> */
    private function fetchActionHistoryRows(int $lastId, string $threshold): array
    {
        return $this->connection->fetchAllAssociative(
            \sprintf(
                <<<'SQL'
                    SELECT h.id AS source_id, h.adherent_id, h.type AS event_type, h.date AS occurred_at, h.data AS metadata_json
                    FROM user_action_history h
                    WHERE h.type IN (:allowedTypes)
                      AND h.id > :lastId
                      AND h.date < :threshold
                    ORDER BY h.id
                    LIMIT %d
                    SQL,
                self::BATCH_SIZE,
            ),
            ['allowedTypes' => AdherentActivityLabels::actionHistoryKeys(), 'lastId' => $lastId, 'threshold' => $threshold],
            ['allowedTypes' => ArrayParameterType::STRING],
        );
    }

    /** @return list<array{source_id: int, adherent_id: int, event_type: string, occurred_at: string, metadata_json: string}> */
    private function fetchHitRows(int $lastId, string $threshold): array
    {
        return $this->connection->fetchAllAssociative(
            \sprintf(
                <<<'SQL'
                    SELECT
                        h.id AS source_id,
                        h.adherent_id,
                        h.event_type,
                        h.created_at AS occurred_at,
                        JSON_OBJECT(
                            'source', h.source,
                            'object_type', h.object_type,
                            'object_id', h.object_id,
                            'object_name', CASE h.object_type
                                WHEN :typeEvent THEN ev.name
                                WHEN :typePublication THEN am.label
                                WHEN :typeTransactional THEN am.label
                                WHEN :typeNews THEN jn.title
                                WHEN :typeAction THEN va.`type`
                                WHEN :typeAlert THEN aa.label
                            END,
                            'button_name', h.button_name,
                            'target_url', h.target_url
                        ) AS metadata_json
                    FROM app_hit h
                    LEFT JOIN `events` ev ON h.object_type = :typeEvent AND ev.uuid = h.object_id
                    LEFT JOIN adherent_messages am ON h.object_type = :typePublication AND am.uuid = h.object_id
                    LEFT JOIN timeline_item_private_message pm ON h.object_type = :typeTransactional AND pm.uuid = h.object_id
                    LEFT JOIN jecoute_news jn ON h.object_type = :typeNews AND jn.uuid = h.object_id
                    LEFT JOIN vox_action va ON h.object_type = :typeAction AND va.uuid = h.object_id
                    LEFT JOIN app_alert aa ON h.object_type = :typeAlert AND aa.uuid = h.object_id
                    WHERE h.event_type IN (:eventTypes)
                      AND h.id > :lastId
                      AND h.created_at < :threshold
                    ORDER BY h.id
                    LIMIT %d
                    SQL,
                self::BATCH_SIZE,
            ),
            [
                'eventTypes' => AdherentActivityLabels::hitEventKeys(),
                'lastId' => $lastId,
                'threshold' => $threshold,
                'typeEvent' => TargetTypeEnum::Event->value,
                'typePublication' => TargetTypeEnum::Publication->value,
                'typeTransactional' => TargetTypeEnum::TransactionalMessage->value,
                'typeNews' => TargetTypeEnum::News->value,
                'typeAction' => TargetTypeEnum::Action->value,
                'typeAlert' => TargetTypeEnum::Alert->value,
            ],
            [
                'eventTypes' => ArrayParameterType::STRING,
            ],
        );
    }

    /**
     * @param array{source_id: int|string, adherent_id: int|string, event_type: string, occurred_at: string, metadata_json: ?string} $row
     *
     * @return array{uuid: string, adherent_id: int, source_type: string, source_id: int, event_type: string, event_label: string, description: ?string, occurred_at: string, metadata_json: ?string, created_at: string}
     */
    private function enrich(array $row, SourceTypeEnum $sourceType): array
    {
        $eventType = $row['event_type'];
        $metadata = null !== $row['metadata_json'] ? json_decode($row['metadata_json'], true, flags: \JSON_THROW_ON_ERROR) : null;

        $eventLabel = AdherentActivityLabels::EVENT_TYPES[$eventType]
            ?? throw new \LogicException(\sprintf('event_type without label: "%s" (label registry out of sync with allowed types)', $eventType));

        return [
            'uuid' => Uuid::v4()->toRfc4122(),
            'adherent_id' => (int) $row['adherent_id'],
            'source_type' => $sourceType->value,
            'source_id' => (int) $row['source_id'],
            'event_type' => $eventType,
            'event_label' => $eventLabel,
            'description' => $this->descriptionBuilder->build($eventType, $metadata),
            'occurred_at' => $row['occurred_at'],
            'metadata_json' => null === $metadata ? null : json_encode($metadata, \JSON_THROW_ON_ERROR),
            'created_at' => new \DateTimeImmutable()->format('Y-m-d H:i:s'),
        ];
    }

    /** @param list<array{uuid: string, adherent_id: int, source_type: string, source_id: int, event_type: string, event_label: string, description: ?string, occurred_at: string, metadata_json: ?string, created_at: string}> $rows */
    private function bulkInsert(array $rows): void
    {
        $placeholders = implode(', ', array_fill(0, \count($rows), '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'));
        $params = [];
        foreach ($rows as $row) {
            $params[] = $row['uuid'];
            $params[] = $row['adherent_id'];
            $params[] = $row['source_type'];
            $params[] = $row['source_id'];
            $params[] = $row['event_type'];
            $params[] = $row['event_label'];
            $params[] = $row['description'];
            $params[] = $row['occurred_at'];
            $params[] = $row['metadata_json'];
            $params[] = $row['created_at'];
        }

        $this->connection->executeStatement(
            \sprintf(
                'INSERT IGNORE INTO adherent_activity (uuid, adherent_id, source_type, source_id, event_type, event_label, description, occurred_at, metadata, created_at) VALUES %s',
                $placeholders,
            ),
            $params,
        );
    }
}
