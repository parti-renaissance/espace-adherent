<?php

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CandidateJecouteMessage;
use App\Entity\AdherentMessage\CoalitionsMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\LreManagerElectedRepresentativeMessage;
use App\Entity\AdherentMessage\PresidentDepartmentalAssemblyAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\RegionalCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\AdherentMessage\StatutoryAdherentMessage;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Security\Voter\FeatureVoter;
use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const COMMITTEE = 'committee';
    public const SENATOR = 'senator';
    public const REFERENT_ELECTED_REPRESENTATIVE = 'referent_elected_representative';
    public const LRE_MANAGER_ELECTED_REPRESENTATIVE = 'lre_manager_elected_representative';
    public const REFERENT_INSTANCES = 'referent_instances';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const CANDIDATE = 'candidate';
    public const CANDIDATE_JECOUTE = 'candidate_jecoute';
    public const COALITIONS = 'coalitions';
    public const CORRESPONDENT = 'correspondent';
    public const REGIONAL_COORDINATOR = 'regional_coordinator';
    public const PRESIDENT_DEPARTMENTAL_ASSEMBLY = 'president_departmental_assembly';
    public const STATUTORY = 'statutory';

    public const CLASSES = [
        self::DEPUTY => DeputyAdherentMessage::class,
        self::REFERENT => ReferentAdherentMessage::class,
        self::COMMITTEE => CommitteeAdherentMessage::class,
        self::SENATOR => SenatorAdherentMessage::class,
        self::REFERENT_ELECTED_REPRESENTATIVE => ReferentElectedRepresentativeMessage::class,
        self::LRE_MANAGER_ELECTED_REPRESENTATIVE => LreManagerElectedRepresentativeMessage::class,
        self::REFERENT_INSTANCES => ReferentInstancesMessage::class,
        self::LEGISLATIVE_CANDIDATE => LegislativeCandidateAdherentMessage::class,
        self::CANDIDATE => CandidateAdherentMessage::class,
        self::CANDIDATE_JECOUTE => CandidateJecouteMessage::class,
        self::COALITIONS => CoalitionsMessage::class,
        self::CORRESPONDENT => CorrespondentAdherentMessage::class,
        self::REGIONAL_COORDINATOR => RegionalCoordinatorAdherentMessage::class,
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY => PresidentDepartmentalAssemblyAdherentMessage::class,
        self::STATUTORY => StatutoryAdherentMessage::class,
    ];

    public const ROLES = [
        DeputyAdherentMessage::class => ['ROLE_DEPUTY', 'ROLE_DELEGATED_DEPUTY'],

        CommitteeAdherentMessage::class => 'ROLE_ANIMATOR',

        SenatorAdherentMessage::class => ['ROLE_SENATOR', 'ROLE_DELEGATED_SENATOR'],

        LreManagerElectedRepresentativeMessage::class => 'ROLE_LRE',

        ReferentAdherentMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],
        ReferentElectedRepresentativeMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],
        ReferentInstancesMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],

        LegislativeCandidateAdherentMessage::class => ['ROLE_LEGISLATIVE_CANDIDATE', 'ROLE_DELEGATED_LEGISLATIVE_CANDIDATE'],

        CandidateAdherentMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],
        CandidateJecouteMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],

        CoalitionsMessage::class => 'ROLE_CAUSE_AUTHOR',

        CorrespondentAdherentMessage::class => ['ROLE_CORRESPONDENT', 'ROLE_DELEGATED_CORRESPONDENT'],

        RegionalCoordinatorAdherentMessage::class => ['ROLE_REGIONAL_COORDINATOR', 'ROLE_DELEGATED_REGIONAL_COORDINATOR'],

        PresidentDepartmentalAssemblyAdherentMessage::class => ['ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY', 'ROLE_DELEGATED_PRESIDENT_DEPARTMENTAL_ASSEMBLY'],

        StatutoryAdherentMessage::class => [[FeatureVoter::PERMISSION, [FeatureEnum::STATUTORY_MESSAGE]]],
    ];

    public static function getMessageClassFromScopeCode(string $scopeCode): ?string
    {
        return self::CLASSES[static::getMessageTypeFromScopeCode($scopeCode)] ?? null;
    }

    public static function getMessageTypeFromScopeCode(string $scopeCode): string
    {
        if (ScopeEnum::ANIMATOR === $scopeCode) {
            return self::COMMITTEE;
        }

        return $scopeCode;
    }
}
