<?php

declare(strict_types=1);

namespace App\Entity\AdherentMandate;

interface AdherentMandateInterface
{
    public const REASON_ELECTION = 'election';
    public const REASON_COMMITTEE_MERGE = 'committee_merge';
    public const REASON_MANUAL = 'manual';
    public const REASON_REPLACED = 'replaced';
}
