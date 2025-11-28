<?php

declare(strict_types=1);

namespace App\VotingPlatform\Election\Enum;

enum ElectionCancelReasonEnum: string
{
    case CandidatesMissing = 'candidates_missing';
    case VotersMissing = 'voters_missing';
    case CommitteeMissing = 'committee_missing';
    case Manual = 'manual';
}
