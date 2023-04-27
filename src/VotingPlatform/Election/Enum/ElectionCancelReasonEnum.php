<?php

namespace App\VotingPlatform\Election\Enum;

enum ElectionCancelReasonEnum: string
{
    case CandidatesMissing = 'candidates_missing';
    case Manual = 'manual';
}
