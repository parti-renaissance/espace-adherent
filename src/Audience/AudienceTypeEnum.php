<?php

namespace App\Audience;

use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;
use MyCLabs\Enum\Enum;

class AudienceTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';

    public const CLASSES = [
        self::DEPUTY => DeputyAudience::class,
        self::REFERENT => ReferentAudience::class,
        self::SENATOR => SenatorAudience::class,
        self::CANDIDATE => CandidateAudience::class,
    ];
}
