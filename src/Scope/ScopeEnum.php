<?php

namespace App\Scope;

use MyCLabs\Enum\Enum;

class ScopeEnum extends Enum
{
    public const REFERENT = 'referent';
    public const DEPUTY = 'deputy';
    public const SENATOR = 'senator';
    public const CANDIDATE = 'candidate';
    public const NATIONAL = 'national';

    private const LABEL_REFERENT = 'Référent';
    private const LABEL_DEPUTY = 'Député';
    private const LABEL_SENATOR = 'Sénateur';
    private const LABEL_CANDIDATE = 'Candidat';
    private const LABEL_NATIONAL = 'National';

    public const LABELS = [
        self::REFERENT => self::LABEL_REFERENT,
        self::DEPUTY => self::LABEL_DEPUTY,
        self::SENATOR => self::LABEL_SENATOR,
        self::CANDIDATE => self::LABEL_CANDIDATE,
        self::NATIONAL => self::LABEL_NATIONAL,
    ];
}
