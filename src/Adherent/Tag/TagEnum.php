<?php

namespace App\Adherent\Tag;

use MyCLabs\Enum\Enum;

class TagEnum extends Enum
{
    public const ADHERENT = 'adherent';
    public const ADHERENT_COTISATION_OK = 'adherent:cotisation_ok';
    public const ADHERENT_COTISATION_NOK = 'adherent:cotisation_nok';

    public const SYMPATHISANT = 'sympathisant';

    public const ELU = 'elu';
    public const ELU_ATTENTE_DECLARATION = 'elu:attente_declaration';

    public const ELU_COTISATION_OK = 'elu:cotisation_ok';
    public const ELU_COTISATION_OK_EXEMPTE = 'elu:cotisation_ok:exempte';
    public const ELU_COTISATION_OK_SOUMIS = 'elu:cotisation_ok:soumis';
    public const ELU_COTISATION_OK_NON_SOUMIS = 'elu:cotisation_ok:non_soumis';

    public const ELU_COTISATION_NOK = 'elu:cotisation_nok';
    public const ELU_EXEMPTE_ET_ADHERENT_COTISATION_NOK = 'elu:exempte_et_adherent_cotisation_nok';

    public static function getAdherentTags(): array
    {
        return [
            self::ADHERENT,
            self::ADHERENT_COTISATION_OK,
            self::ADHERENT_COTISATION_NOK,
            self::SYMPATHISANT,
        ];
    }
}
