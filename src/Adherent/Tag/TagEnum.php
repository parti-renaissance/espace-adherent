<?php

namespace App\Adherent\Tag;

use MyCLabs\Enum\Enum;

class TagEnum extends Enum
{
    public const ADHERENT_COTISATION_OK = 'adherent:cotisation_ok';
    public const ADHERENT_COTISATION_NOK = 'adherent:cotisation_nok';

    public const SYMPATHISANT_COMPTE_EM = 'sympathisant:compte_em';
    public const SYMPATHISANT_COMPTE_RE = 'sympathisant:compte_re';
}
