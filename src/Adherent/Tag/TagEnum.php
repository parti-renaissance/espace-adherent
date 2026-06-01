<?php

declare(strict_types=1);

namespace App\Adherent\Tag;

use MyCLabs\Enum\Enum;

class TagEnum extends Enum
{
    public const CONTACT = 'contact';
    public const USER = 'user';
    public const SYMPATHISANT = 'sympathisant';
    public const ADHERENT = 'adherent';

    public const ADHERENT_YEAR_TAG_PATTERN = self::ADHERENT.':a_jour_%s';
    public const ADHERENT_YEAR_PRIMO_TAG_PATTERN = self::ADHERENT_YEAR_TAG_PATTERN.':primo';
    public const ADHERENT_YEAR_RECOTISATION_TAG_PATTERN = self::ADHERENT_YEAR_TAG_PATTERN.':recotisation';
    public const ADHERENT_YEAR_ELU_TAG_PATTERN = self::ADHERENT_YEAR_TAG_PATTERN.':elu_a_jour';

    public const ADHERENT_NOT_UP_TO_DATE = self::ADHERENT.':plus_a_jour';
    public const ADHERENT_NOT_UP_TO_DATE_TAG_PATTERN = self::ADHERENT_NOT_UP_TO_DATE.':annee_%s';

    public const ELU = 'elu';
    public const ELU_ATTENTE_DECLARATION = 'elu:attente_declaration';

    public const ELU_COTISATION_OK = 'elu:cotisation_ok';
    public const ELU_COTISATION_OK_EXEMPTE = 'elu:cotisation_ok:exempte';
    public const ELU_COTISATION_OK_SOUMIS = 'elu:cotisation_ok:soumis';
    public const ELU_COTISATION_OK_NON_SOUMIS = 'elu:cotisation_ok:non_soumis';

    public const ELU_COTISATION_NOK = 'elu:cotisation_nok';
    public const ELU_EXEMPTE_ET_ADHERENT_COTISATION_NOK = 'elu:exempte_et_adherent_cotisation_nok';

    public const NATIONAL_EVENT = 'national_event';
    public const NATIONAL_EVENT_PRESENT = self::NATIONAL_EVENT.':present:';
    public const NATIONAL_EVENT_PATTERN = self::NATIONAL_EVENT.':%s';
    public const NATIONAL_EVENT_PRESENT_PATTERN = self::NATIONAL_EVENT_PRESENT.'%s';

    public static function getAdherentTags(bool $adherentOnly = false): array
    {
        $currentYear = (int) date('Y');

        $adherentTags = array_merge(
            [
                self::ADHERENT,
                self::getAdherentYearTag($currentYear),
                \sprintf(self::ADHERENT_YEAR_PRIMO_TAG_PATTERN, $currentYear),
                \sprintf(self::ADHERENT_YEAR_RECOTISATION_TAG_PATTERN, $currentYear),
                \sprintf(self::ADHERENT_YEAR_ELU_TAG_PATTERN, $currentYear),
                self::ADHERENT_NOT_UP_TO_DATE,
            ],
            array_map(
                fn (int $year) => self::getAdherentYearTag($year),
                array_reverse(range(2022, $currentYear - 1))
            )
        );

        return $adherentOnly ? $adherentTags : array_merge($adherentTags, [
            self::SYMPATHISANT,
            self::CONTACT,
            self::USER,
        ]);
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

    public static function getAdherentYearTag(?int $year = null, ?string $tag = null): string
    {
        $currentYear = date('Y');

        if (!$tag) {
            if (!$year || $currentYear == $year) {
                $tag = self::ADHERENT_YEAR_TAG_PATTERN;
            } else {
                $tag = self::ADHERENT_NOT_UP_TO_DATE_TAG_PATTERN;
            }
        }

        return \sprintf($tag, $year ?? $currentYear);
    }

    public static function includesTag(string $searchTag, array $previousTags): bool
    {
        foreach ($previousTags as $tag) {
            if (str_starts_with($tag, $searchTag)) {
                return true;
            }
        }

        return false;
    }

    public static function getMainLevel(string $tag): string
    {
        return explode(':', $tag)[0];
    }

    /**
     * A flat tag whose ":" descendants (not the tag itself) are stored on adherents.
     * Such a parent needs a boundary when matched in a substring context (e.g. Mailchimp
     * "contains"). Flat *leaf* tags stored directly (e.g. SYMPATHISANT) are not roots and
     * match their exact label — adding a new childless tag requires no change here.
     */
    public static function isHierarchicalRoot(string $tag): bool
    {
        return \in_array($tag, [self::ADHERENT, self::ELU, self::NATIONAL_EVENT], true);
    }

    public static function getNationalEventTag(string $eventSlug, bool $isPresent): string
    {
        return \sprintf($isPresent ? self::NATIONAL_EVENT_PRESENT_PATTERN : self::NATIONAL_EVENT_PATTERN, $eventSlug);
    }
}
