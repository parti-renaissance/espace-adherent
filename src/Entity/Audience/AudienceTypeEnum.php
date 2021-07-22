<?php

namespace App\Entity\Audience;

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
