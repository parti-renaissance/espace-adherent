<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

/**
 * One resolved view-filter condition. Conditions accumulate (AND), mirroring the Algolia $userFilter
 * built by GetTimelineFeedsController: the zone/committee/instance query params may combine, and two
 * conditions of the same kind may coexist (e.g. zone param + instance=assembly).
 */
class RequestFilterCondition
{
    public const string NATIONAL = 'national';   // zone param with an unresolvable uuid (Algolia quirk)
    public const string ZONE = 'zone';           // value = "type:code" (canonical mirror shape)
    public const string COMMITTEE = 'committee'; // value = committee uuid
    public const string AGORA = 'agora';         // value = agora uuid

    public function __construct(
        public readonly string $kind,
        public readonly ?string $value = null,
    ) {
    }
}
