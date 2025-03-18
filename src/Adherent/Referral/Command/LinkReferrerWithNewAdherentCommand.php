<?php

namespace App\Adherent\Referral\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class LinkReferrerWithNewAdherentCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly ?string $referrerPublicId = null,
        public readonly ?string $referralIdentifier = null,
    ) {
        parent::__construct($uuid);

        if (!$referrerPublicId && !$referralIdentifier) {
            throw new \InvalidArgumentException(sprintf('At least one of referrerPublicId or referralIdentifier must be provided. (%s)', $uuid));
        }
    }
}
