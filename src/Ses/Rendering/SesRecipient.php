<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

/**
 * A single campaign recipient, as projected by the fan-out query
 * (MailchimpStaticSegmentMemberRepository::findClaimableRecipientsByChunk).
 */
class SesRecipient
{
    public function __construct(
        public readonly string $email,
        public readonly string $uuid,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $gender = null,
        public readonly ?string $publicId = null,
        public readonly ?int $memberRowId = null,
    ) {
    }
}
