<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

readonly class PopulateAdherentActivityService
{
    private const int BATCH_SIZE = 10_000;

    private const array ALLOWED_HIT_EVENT_TYPES = [
        EventTypeEnum::Open->value,
        EventTypeEnum::Click->value,
        EventTypeEnum::ActivitySession->value,
    ];

    private const array ALLOWED_ACTION_HISTORY_TYPES = [
        UserActionHistoryTypeEnum::LOGIN_SUCCESS->value,
        UserActionHistoryTypeEnum::LOGIN_FAILURE->value,
        UserActionHistoryTypeEnum::PROFILE_UPDATE->value,
        UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST->value,
        UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE->value,
        UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST->value,
        UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE->value,
        UserActionHistoryTypeEnum::ROLE_ADD->value,
        UserActionHistoryTypeEnum::ROLE_REMOVE->value,
        UserActionHistoryTypeEnum::LIVE_VIEW->value,
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD->value,
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_EDIT->value,
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_REMOVE->value,
        UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_ADD->value,
        UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_REMOVE->value,
        UserActionHistoryTypeEnum::AGORA_PRESIDENT_ADD->value,
        UserActionHistoryTypeEnum::AGORA_PRESIDENT_REMOVE->value,
        UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_ADD->value,
        UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_REMOVE->value,
        UserActionHistoryTypeEnum::MEMBERSHIP_ANNIVERSARY_REMINDED->value,
        UserActionHistoryTypeEnum::COMMITTEE_CREATE->value,
        UserActionHistoryTypeEnum::COMMITTEE_UPDATE->value,
        UserActionHistoryTypeEnum::COMMITTEE_DELETE->value,
    ];

    public function __construct(private Connection $connection)
    {
    }

    public function processBatch(SourceTypeEnum $sourceType): bool
    {
        $lastId = (int) $this->connection->fetchOne(
            'SELECT COALESCE(MAX(source_id), 0) FROM adherent_activity WHERE source_type = :sourceType',
            ['sourceType' => $sourceType->value],
        );

        $inserted = match ($sourceType) {
            SourceTypeEnum::ActionHistory => $this->insertFromActionHistory($lastId),
            SourceTypeEnum::Hit => $this->insertFromHits($lastId),
        };

        return $inserted >= self::BATCH_SIZE;
    }

    private function insertFromActionHistory(int $lastId): int
    {
        return (int) $this->connection->executeStatement(
            \sprintf(
                <<<'SQL'
                    INSERT IGNORE INTO adherent_activity (uuid, adherent_id, source_type, source_id, event_type, occurred_at, metadata, created_at)
                    SELECT UUID(), h.adherent_id, 'action_history', h.id, h.type, h.date, h.data, NOW()
                    FROM user_action_history h
                    WHERE h.type IN (:allowedTypes)
                      AND h.id > :lastId
                    ORDER BY h.id
                    LIMIT %d
                    SQL,
                self::BATCH_SIZE,
            ),
            ['allowedTypes' => self::ALLOWED_ACTION_HISTORY_TYPES, 'lastId' => $lastId],
            ['allowedTypes' => ArrayParameterType::STRING],
        );
    }

    private function insertFromHits(int $lastId): int
    {
        return (int) $this->connection->executeStatement(
            \sprintf(
                <<<'SQL'
                    INSERT IGNORE INTO adherent_activity (uuid, adherent_id, source_type, source_id, event_type, occurred_at, metadata, created_at)
                    SELECT
                        UUID(),
                        h.adherent_id,
                        'hit',
                        h.id,
                        h.event_type,
                        h.app_date,
                        JSON_OBJECT(
                            'source', h.source,
                            'object_type', h.object_type,
                            'object_id', h.object_id,
                            'object_name', CASE h.object_type
                                WHEN 'event' THEN ev.name
                                WHEN 'publication' THEN am.label
                                WHEN 'transactional_message' THEN am.label
                                WHEN 'news' THEN jn.title
                                WHEN 'action' THEN va.`type`
                                WHEN 'alert' THEN aa.label
                            END,
                            'button_name', h.button_name,
                            'target_url', h.target_url
                        ),
                        NOW()
                    FROM app_hit h
                    LEFT JOIN `events` ev ON h.object_type = 'event' AND ev.uuid = h.object_id
                    LEFT JOIN adherent_messages am ON h.object_type IN ('publication', 'transactional_message') AND am.uuid = h.object_id
                    LEFT JOIN jecoute_news jn ON h.object_type = 'news' AND jn.uuid = h.object_id
                    LEFT JOIN vox_action va ON h.object_type = 'action' AND va.uuid = h.object_id
                    LEFT JOIN app_alert aa ON h.object_type = 'alert' AND aa.uuid = h.object_id
                    WHERE h.event_type IN (:eventTypes)
                      AND h.adherent_id IS NOT NULL
                      AND h.id > :lastId
                    ORDER BY h.id
                    LIMIT %d
                    SQL,
                self::BATCH_SIZE,
            ),
            ['eventTypes' => self::ALLOWED_HIT_EVENT_TYPES, 'lastId' => $lastId],
            ['eventTypes' => ArrayParameterType::STRING],
        );
    }
}
