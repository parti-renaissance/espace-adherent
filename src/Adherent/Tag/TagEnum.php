<?php

namespace App\Adherent\Tag;

use MyCLabs\Enum\Enum;

class TagEnum extends Enum
{
    public const ADHERENT = 'adherent';
    public const ADHERENT_COTISATION_OK = 'adherent:cotisation_ok';
    public const ADHERENT_COTISATION_NOK = 'adherent:cotisation_nok';

    public const SYMPATHISANT = 'sympathisant';
    public const SYMPATHISANT_COMPTE_EM = 'sympathisant:compte_em';
    public const SYMPATHISANT_COMPTE_RE = 'sympathisant:compte_re';

    public const ELU = 'elu';
    public const ELU_DECLARATION_OK = 'elu:declaration_ok';
    public const ELU_COTISATION_ELIGIBLE = 'elu:cotisation_eligible';
    public const ELU_COTISATION_OK = 'elu:cotisation_ok';
    public const ELU_EXEMPTE = 'elu:exempte';
}
