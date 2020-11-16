<?php

namespace App\Entity\TerritorialCouncil;

use MyCLabs\Enum\Enum;

class TerritorialCouncilQualityEnum extends Enum
{
    public const REFERENT = 'referent';
    public const GOVERNMENT_MEMBER = 'government_member';
    public const REFERENT_JAM = 'referent_jam';
    public const LRE_MANAGER = 'lre_manager';
    public const SENATOR = 'senator';
    public const DEPUTY = 'deputy';
    public const EUROPEAN_DEPUTY = 'european_deputy';
    public const REGIONAL_COUNCIL_PRESIDENT = 'regional_council_president';
    public const DEPARTMENTAL_COUNCIL_PRESIDENT = 'departmental_council_president';
    public const MAYOR = 'mayor';
    public const LEADER = 'leader';
    public const REGIONAL_COUNCILOR = 'regional_councilor';
    public const DEPARTMENT_COUNCILOR = 'department_councilor';
    public const CITY_COUNCILOR = 'city_councilor';
    public const BOROUGH_COUNCILOR = 'borough_councilor';
    public const CONSULAR_COUNCILOR = 'consular_councilor';
    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const ELECTED_CANDIDATE_ADHERENT = 'elected_candidate_adherent';

    public const FORBIDDEN_TO_CANDIDATE = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::LRE_MANAGER,
        self::SENATOR,
        self::DEPUTY,
        self::EUROPEAN_DEPUTY,
        self::REGIONAL_COUNCIL_PRESIDENT,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT,
    ];

    public const ABLE_TO_CANDIDATE = [
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];

    public const QUALITY_PRIORITIES = [
        self::REFERENT => 0,
        self::GOVERNMENT_MEMBER => 1,
        self::REFERENT_JAM => 2,
        self::LRE_MANAGER => 3,
        self::SENATOR => 4,
        self::DEPUTY => 5,
        self::EUROPEAN_DEPUTY => 6,
        self::REGIONAL_COUNCIL_PRESIDENT => 7,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT => 8,
        self::MAYOR => 9,
        self::REGIONAL_COUNCILOR => 10,
        self::DEPARTMENT_COUNCILOR => 11,
        self::CITY_COUNCILOR => 12,
        self::BOROUGH_COUNCILOR => 13,
        self::CONSULAR_COUNCILOR => 14,
        self::COMMITTEE_SUPERVISOR => 15,
        self::ELECTED_CANDIDATE_ADHERENT => 16,
    ];

    public const ALL = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::LRE_MANAGER,
        self::SENATOR,
        self::DEPUTY,
        self::EUROPEAN_DEPUTY,
        self::REGIONAL_COUNCIL_PRESIDENT,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT,
        self::MAYOR,
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];

    public const ALL_POLITICAL_COMMITTEE_QUALITIES = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::LRE_MANAGER,
        self::SENATOR,
        self::DEPUTY,
        self::EUROPEAN_DEPUTY,
        self::REGIONAL_COUNCIL_PRESIDENT,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT,
        self::MAYOR,
        self::LEADER,
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];

    public const POLITICAL_COMMITTEE_OFFICIO_MEMBERS = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::LRE_MANAGER,
        self::SENATOR,
        self::DEPUTY,
        self::EUROPEAN_DEPUTY,
        self::REGIONAL_COUNCIL_PRESIDENT,
        self::DEPARTMENTAL_COUNCIL_PRESIDENT,
    ];

    public const POLITICAL_COMMITTEE_MANAGED_IN_ADMIN_MEMBERS = [
        self::REFERENT,
        self::GOVERNMENT_MEMBER,
        self::REFERENT_JAM,
        self::LRE_MANAGER,
    ];

    public const POLITICAL_COMMITTEE_ELECTED_MEMBERS = [
        self::REGIONAL_COUNCILOR,
        self::DEPARTMENT_COUNCILOR,
        self::CITY_COUNCILOR,
        self::BOROUGH_COUNCILOR,
        self::CONSULAR_COUNCILOR,
        self::COMMITTEE_SUPERVISOR,
        self::ELECTED_CANDIDATE_ADHERENT,
    ];

    public const ALL_TC_PC_CHOICES = [
        'territorial_council.membership.quality.referent' => self::REFERENT,
        'territorial_council.membership.quality.government_member' => self::GOVERNMENT_MEMBER,
        'territorial_council.membership.quality.referent_jam' => self::REFERENT_JAM,
        'territorial_council.membership.quality.lre_manager' => self::LRE_MANAGER,
        'territorial_council.membership.quality.senator' => self::SENATOR,
        'territorial_council.membership.quality.deputy' => self::DEPUTY,
        'territorial_council.membership.quality.european_deputy' => self::EUROPEAN_DEPUTY,
        'territorial_council.membership.quality.regional_council_president' => self::REGIONAL_COUNCIL_PRESIDENT,
        'territorial_council.membership.quality.departmental_council_president' => self::DEPARTMENTAL_COUNCIL_PRESIDENT,
        'territorial_council.membership.quality.mayor' => self::MAYOR,
        'territorial_council.membership.quality.regional_councilor' => self::REGIONAL_COUNCILOR,
        'territorial_council.membership.quality.department_councilor' => self::DEPARTMENT_COUNCILOR,
        'territorial_council.membership.quality.city_councilor' => self::CITY_COUNCILOR,
        'territorial_council.membership.quality.borough_councilor' => self::BOROUGH_COUNCILOR,
        'territorial_council.membership.quality.consular_councilor' => self::CONSULAR_COUNCILOR,
        'territorial_council.membership.quality.committee_supervisor' => self::COMMITTEE_SUPERVISOR,
        'territorial_council.membership.quality.elected_candidate_adherent' => self::ELECTED_CANDIDATE_ADHERENT,
        'political_committee.membership.quality.leader' => 'PC_'.self::LEADER,
        'political_committee.membership.quality.regional_councilor' => 'PC_'.self::REGIONAL_COUNCILOR,
        'political_committee.membership.quality.department_councilor' => 'PC_'.self::DEPARTMENT_COUNCILOR,
        'political_committee.membership.quality.city_councilor' => 'PC_'.self::CITY_COUNCILOR,
        'political_committee.membership.quality.borough_councilor' => 'PC_'.self::BOROUGH_COUNCILOR,
        'political_committee.membership.quality.consular_councilor' => 'PC_'.self::CONSULAR_COUNCILOR,
        'political_committee.membership.quality.committee_supervisor' => 'PC_'.self::COMMITTEE_SUPERVISOR,
        'political_committee.membership.quality.elected_candidate_adherent' => 'PC_'.self::ELECTED_CANDIDATE_ADHERENT,
    ];
}
