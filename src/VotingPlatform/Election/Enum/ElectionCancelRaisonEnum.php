<?php

namespace App\VotingPlatform\Election\Enum;

enum ElectionCancelRaisonEnum: string
{
    case CandidatesMissing = 'candidates_missing';
}
