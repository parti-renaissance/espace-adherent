<?php

declare(strict_types=1);

namespace App\Ses\Client;

/**
 * Input value object for a single SES SendEmail call: a fully-rendered email for one recipient.
 */
class SesEmail
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $html,
        public readonly string $fromEmail,
        public readonly ?string $fromName = null,
        public readonly ?string $replyTo = null,
        public readonly ?string $listUnsubscribeUrl = null,
        public readonly ?string $campaignUuid = null,
        public readonly ?string $adherentUuid = null,
    ) {
    }
}
