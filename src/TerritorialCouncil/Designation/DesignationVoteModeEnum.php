<?php

namespace App\TerritorialCouncil\Designation;

use MyCLabs\Enum\Enum;

class DesignationVoteModeEnum extends Enum
{
    public const VOTE_MODE_ONLINE = 'online';
    public const VOTE_MODE_MEETING = 'meeting';

    public const ALL = [
        self::VOTE_MODE_ONLINE,
        self::VOTE_MODE_MEETING,
    ];
}
