<?php

declare(strict_types=1);

namespace App\Adherent\Referral\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Symfony\Component\Uid\Uuid;

class LinkReferrerWithNewAdherentCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        Uuid $uuid,
        public readonly bool $fromCotisation,
        public readonly ?string $referrerPublicId = null,
        public readonly ?string $referralIdentifier = null,
    ) {
        parent::__construct($uuid);
    }
}
