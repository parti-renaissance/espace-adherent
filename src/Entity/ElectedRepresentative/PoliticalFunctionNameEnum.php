<?php

namespace AppBundle\Entity\ElectedRepresentative;

use MyCLabs\Enum\Enum;

final class PoliticalFunctionNameEnum extends Enum
{
    public const MAYOR = 'mayor';
    public const DEPUTY_MAYOR = 'deputy_mayor';
    public const MAYOR_ASSISTANT = 'mayor_assistant';
    public const PRESIDENT_OF_REGIONAL_COUNCIL = 'president_of_regional_council';
    public const VICE_PRESIDENT_OF_REGIONAL_COUNCIL = 'vice_president_of_regional_council';
    public const PRESIDENT_OF_DEPARTMENTAL_COUNCIL = 'president_of_departmental_council';
    public const VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL = 'vice_president_of_departmental_council';
    public const SECRETARY = 'secretary';
    public const QUAESTOR = 'quaestor';
    public const PRESIDENT_OF_NATIONAL_ASSEMBLY = 'president_of_national_assembly';
    public const VICE_PRESIDENT_OF_NATIONAL_ASSEMBLY = 'vice_president_of_national_assembly';
    public const PRESIDENT_OF_SENATE = 'president_of_senate';
    public const VICE_PRESIDENT_OF_SENATE = 'vice_president_of_senate';
    public const PRESIDENT_OF_COMMISSION = 'president_of_commission';
    public const PRESIDENT_OF_GROUP = 'president_of_group';
    public const PRESIDENT_OF_EPCI = 'president_of_epci';
    public const VICE_PRESIDENT_OF_EPCI = 'vice_president_of_epci';
    public const DEPUTY_VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL = 'deputy_vice_president_of_departmental_council';
    public const OTHER_MEMBER_OF_STANDING_COMMITTEE = 'other_member_of_standing_committee';
    public const OTHER_MEMBER = 'other_member';

    public const MAYOR_LABEL = 'Maire';
    public const DEPUTY_MAYOR_LABEL = 'Maire délégué(e)';
    public const MAYOR_ASSISTANT_LABEL = 'Adjoint(e) au maire';
    public const PRESIDENT_OF_REGIONAL_COUNCIL_LABEL = 'Président(e) de conseil régional';
    public const VICE_PRESIDENT_OF_REGIONAL_COUNCIL_LABEL = 'Vice-président(e) de conseil régional';
    public const PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL = 'Président(e) de conseil départemental';
    public const VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL = 'Vice-président(e) de conseil départemental';
    public const DEPUTY_VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL = 'Vice-président(e) délégué du conseil départemental';
    public const SECRETARY_LABEL = 'Secrétaire';
    public const QUAESTOR_LABEL = 'Questeur(rice)';
    public const PRESIDENT_OF_NATIONAL_ASSEMBLY_LABEL = 'Président(e) de l\'Assemblée nationale';
    public const VICE_PRESIDENT_OF_NATIONAL_ASSEMBLY_LABEL = 'Vice-président(e) de l\'Assemblée nationale';
    public const PRESIDENT_OF_SENATE_LABEL = 'Président(e) du Sénat';
    public const VICE_PRESIDENT_OF_SENATE_LABEL = 'Vice-président(e) du Sénat';
    public const PRESIDENT_OF_COMMISSION_LABEL = 'Président(e) de commission';
    public const PRESIDENT_OF_GROUP_LABEL = 'Président(e) de groupe';
    public const PRESIDENT_OF_EPCI_LABEL = 'Président(e) d\'EPCI';
    public const VICE_PRESIDENT_OF_EPCI_LABEL = 'Vice-président(e) d\'EPCI';
    public const OTHER_MEMBER_OF_STANDING_COMMITTEE_LABEL = 'Autre membre commission permanente';
    public const OTHER_MEMBER_LABEL = 'Autre membre';

    public const CHOICES = [
        self::MAYOR_LABEL => self::MAYOR,
        self::DEPUTY_MAYOR_LABEL => self::DEPUTY_MAYOR,
        self::MAYOR_ASSISTANT_LABEL => self::MAYOR_ASSISTANT,
        self::PRESIDENT_OF_REGIONAL_COUNCIL_LABEL => self::PRESIDENT_OF_REGIONAL_COUNCIL,
        self::VICE_PRESIDENT_OF_REGIONAL_COUNCIL_LABEL => self::VICE_PRESIDENT_OF_REGIONAL_COUNCIL,
        self::PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL => self::PRESIDENT_OF_DEPARTMENTAL_COUNCIL,
        self::VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL => self::VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL,
        self::DEPUTY_VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL_LABEL => self::DEPUTY_VICE_PRESIDENT_OF_DEPARTMENTAL_COUNCIL,
        self::SECRETARY_LABEL => self::SECRETARY,
        self::QUAESTOR_LABEL => self::QUAESTOR,
        self::PRESIDENT_OF_NATIONAL_ASSEMBLY_LABEL => self::PRESIDENT_OF_NATIONAL_ASSEMBLY,
        self::VICE_PRESIDENT_OF_NATIONAL_ASSEMBLY_LABEL => self::VICE_PRESIDENT_OF_NATIONAL_ASSEMBLY,
        self::PRESIDENT_OF_SENATE_LABEL => self::PRESIDENT_OF_SENATE,
        self::VICE_PRESIDENT_OF_SENATE_LABEL => self::VICE_PRESIDENT_OF_SENATE,
        self::PRESIDENT_OF_COMMISSION_LABEL => self::PRESIDENT_OF_COMMISSION,
        self::PRESIDENT_OF_GROUP_LABEL => self::PRESIDENT_OF_GROUP,
        self::PRESIDENT_OF_EPCI_LABEL => self::PRESIDENT_OF_EPCI,
        self::VICE_PRESIDENT_OF_EPCI_LABEL => self::VICE_PRESIDENT_OF_EPCI,
        self::OTHER_MEMBER_OF_STANDING_COMMITTEE_LABEL => self::OTHER_MEMBER_OF_STANDING_COMMITTEE,
        self::OTHER_MEMBER_LABEL => self::OTHER_MEMBER,
    ];
}
