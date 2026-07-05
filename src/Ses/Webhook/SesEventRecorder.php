<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Doctrine\ORM\EntityManagerInterface;

class SesEventRecorder
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function record(SesRawEventData $data, \DateTimeImmutable $receivedAt): void
    {
        if ('' === $data->snsMessageId) {
            return;
        }

        $sql = <<<'SQL'
            INSERT INTO ses_event
                (sns_message_id, event_type, ses_message_id, campaign_uuid, adherent_uuid, recipient, occurred_at, received_at, payload)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE sns_message_id = sns_message_id
            SQL;

        $this->entityManager->getConnection()->executeStatement($sql, [
            $data->snsMessageId,
            $data->eventType,
            $data->sesMessageId,
            $data->campaignUuid,
            $data->adherentUuid,
            $data->recipient,
            $data->occurredAt?->format('Y-m-d H:i:s'),
            $receivedAt->format('Y-m-d H:i:s'),
            json_encode($data->payload, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
        ]);
    }
}
