<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;

class SendStatusFactory
{
    public function build(MailchimpCampaign $campaign): array
    {
        $segment = $campaign->getMailchimpStaticSegment();
        $expected = $segment?->expectedCount;
        $prepared = $segment?->preparedCount;

        return [
            'preparation_status' => $campaign->getPreparationStatus()->value,
            'audience_check' => $campaign->getAudienceCheck()?->value,
            'block_reason' => $campaign->getBlockReason()?->value,
            'can_send' => $campaign->canSend() && !$this->messageAlreadySent($campaign),
            'cancellation_requested' => $campaign->isCancellationRequested(),
            'counts' => [
                'expected' => $expected,
                'prepared' => $prepared,
                'diff' => null !== $expected && null !== $prepared ? $prepared - $expected : null,
            ],
            'prepared_at' => $campaign->getPreparedAt()?->format(\DATE_ATOM),
            'blocking_user' => $campaign->getPreparationLockedBy(),
            // failure_detail intentionally NOT exposed here: DESIGN §5.1 states
            // "logged, not displayed". block_reason is enough for UX; the
            // technical detail stays in server logs (Phase 5 handler logger->error).
            'progress' => [
                'chunks_done' => $segment?->chunksDone ?? 0,
            ],
        ];
    }

    private function messageAlreadySent(MailchimpCampaign $campaign): bool
    {
        $message = $campaign->getMessage();

        return $message instanceof AdherentMessage && $message->isSent();
    }
}
