<?php

namespace App\VotingPlatform\Election\Enum;

enum ElectionCancelReasonEnum: string
{
    case CandidatesMissing = 'candidates_missing';
    case CommitteeMissing = 'committee_missing';
    case Manual = 'manual';
}
