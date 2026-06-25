<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\CandidateSelection;

/**
 * Resolved view filter of a timeline request (query params zone/committee/instance).
 */
class TimelineRequestFilter
{
    /**
     * @param list<RequestFilterCondition> $conditions ANDed by the matcher
     */
    public function __construct(public readonly array $conditions)
    {
    }
}
