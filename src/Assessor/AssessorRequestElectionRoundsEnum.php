<?php

namespace App\Assessor;

use MyCLabs\Enum\Enum;

class AssessorRequestElectionRoundsEnum extends Enum
{
    public const FIRST_ROUND = 'first_round';
    public const SECOND_ROUND = 'second_round';

    public const ALL = [
        self::FIRST_ROUND,
        self::SECOND_ROUND,
    ];

    public const CHOICES = [
        'assessor_request.election_rounds.first_round' => self::FIRST_ROUND,
        'assessor_request.election_rounds.second_round' => self::SECOND_ROUND,
    ];
}
