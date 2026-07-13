<?php

declare(strict_types=1);

namespace App\Ses\Campaign\Reach;

use App\Doctrine\Utils\BulkInsertHelper;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;

/**
 * Records the campaign reach once the SES send is complete, sourced from the audience staging rows
 * actually sent (red-team #4) — there is no provider report to poll. INSERT IGNORE makes it idempotent
 * against the unique (adherent, message, source) key, so a redelivered completion never duplicates rows.
 */
class CampaignReachInserter
{
    private const string SOURCE = 'email';
    // Same batch size as the audience staging insert (PrepareCampaignAudienceHandler): a national send
    // can reach 150k recipients, too many tuples for a single INSERT (max_allowed_packet, lock size).
    private const int INSERT_BATCH = 5_000;

    public function __construct(
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly BulkInsertHelper $bulkInsertHelper,
    ) {
    }

    public function insertFromSentRows(int $staticSegmentId, int $messageId): void
    {
        $adherentIds = $this->memberRepository->findSentAdherentIds($staticSegmentId);
        if ([] === $adherentIds) {
            return;
        }

        $now = new \DateTime()->format('Y-m-d H:i:s');

        foreach (array_chunk($adherentIds, self::INSERT_BATCH) as $batch) {
            $rows = [];
            foreach ($batch as $adherentId) {
                $rows[] = [
                    'message_id' => $messageId,
                    'adherent_id' => $adherentId,
                    'source' => self::SOURCE,
                    'date' => $now,
                ];
            }

            $this->bulkInsertHelper->insertIgnore('adherent_message_reach', $rows);
        }
    }

    public function insertOne(int $messageId, int $adherentId): void
    {
        $this->bulkInsertHelper->insertIgnore('adherent_message_reach', [[
            'message_id' => $messageId,
            'adherent_id' => $adherentId,
            'source' => self::SOURCE,
            'date' => new \DateTime()->format('Y-m-d H:i:s'),
        ]]);
    }
}
