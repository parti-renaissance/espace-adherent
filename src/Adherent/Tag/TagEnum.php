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
    public const SYMPATHISANT_ADHESION_INCOMPLETE = 'sympathisant:adhesion_incomplete';
    public const SYMPATHISANT_AUTRE_PARTI = 'sympathisant:autre_parti';

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
            self::SYMPATHISANT_ADHESION_INCOMPLETE,
            self::SYMPATHISANT_COMPTE_EM,
            self::SYMPATHISANT_AUTRE_PARTI,
        ];
    }

    public static function getElectTags(): array
    {
        return [
            self::ELU,
            self::ELU_ATTENTE_DECLARATION,
            self::ELU_COTISATION_OK,
            self::ELU_COTISATION_OK_EXEMPTE,
            self::ELU_COTISATION_OK_NON_SOUMIS,
            self::ELU_COTISATION_OK_SOUMIS,
            self::ELU_COTISATION_NOK,
            self::ELU_EXEMPTE_ET_ADHERENT_COTISATION_NOK,
        ];
    }

    public static function getMCTagLabels(): array
    {
        return [
            self::ADHERENT => 'adherent',
            self::ADHERENT_COTISATION_OK => 'adherent:à jour de cotisation',
            self::ADHERENT_COTISATION_NOK => 'adherent:non à jour de cotisation',
            self::SYMPATHISANT => 'sympathisant',
            self::SYMPATHISANT_ADHESION_INCOMPLETE => 'sympathisant:adhésion incomplète',
            self::SYMPATHISANT_COMPTE_EM => 'sympathisant:ancien adhérent En Marche',
            self::SYMPATHISANT_AUTRE_PARTI => 'sympathisant:adhérent d\'un autre parti',
            self::ELU => 'elu',
            self::ELU_ATTENTE_DECLARATION => 'elu:en attente de déclaration',
            self::ELU_COTISATION_OK => 'elu:à jour de cotisation',
            self::ELU_COTISATION_OK_EXEMPTE => 'elu:à jour de cotisation:exempté de cotisation',
            self::ELU_COTISATION_OK_NON_SOUMIS => 'elu:à jour de cotisation:non soumis à cotisation',
            self::ELU_COTISATION_OK_SOUMIS => 'elu:à jour de cotisation:soumis à cotisation',
            self::ELU_COTISATION_NOK => 'elu:non à jour de cotisation',
            self::ELU_EXEMPTE_ET_ADHERENT_COTISATION_NOK => 'elu:exempté mais pas à jour de cotisation adhérent',
        ];
    }

    public static function getReducedTags(array $allTags): array
    {
        $reducedTags = [];

        foreach ($allTags as $tag) {
            $found = false;

            foreach ($allTags as $tmpTag) {
                if (str_contains($tmpTag, $tag.':')) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $reducedTags[] = $tag;
            }
        }

        usort($reducedTags, static function (string $tag): int {
            if (str_starts_with('adherent', $tag)) {
                return 1;
            } elseif (str_starts_with('sympathisant', $tag)) {
                return 0;
            } else {
                return -1;
            }
        });

        return $reducedTags;
    }
}
