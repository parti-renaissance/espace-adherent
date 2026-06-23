<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

/**
 * Output of SesMessageAssembler: the message-level email, assembled once per publication.
 *
 * The HTML carries the chrome + body + resolved source sections, but still contains the
 * recipient-level Dictionary placeholders ({{Prénom}}, {{Chère/Cher Prénom}}, …) which are
 * resolved per recipient by SesRecipientEmailFactory (Phase 5).
 */
class AssembledCampaignEmail
{
    public function __construct(
        public readonly string $html,
        public readonly string $subject,
        public readonly string $fromEmail,
        public readonly ?string $fromName = null,
        public readonly ?string $replyTo = null,
        public readonly ?string $campaignUuid = null,
    ) {
    }
}
