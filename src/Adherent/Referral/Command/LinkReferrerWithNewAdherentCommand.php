<?php

namespace App\Adherent\Referral\Command;

use App\Messenger\Message\UuidDefaultAsyncMessage;
use Ramsey\Uuid\UuidInterface;

class LinkReferrerWithNewAdherentCommand extends UuidDefaultAsyncMessage
{
    public function __construct(
        UuidInterface $uuid,
        public readonly string $referrerPublicId,
    ) {
        parent::__construct($uuid);
    }
}
