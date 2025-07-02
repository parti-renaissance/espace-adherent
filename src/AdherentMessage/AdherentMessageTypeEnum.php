<?php

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CandidateJecouteMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\FdeCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\PresidentDepartmentalAssemblyAdherentMessage;
use App\Entity\AdherentMessage\RegionalCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\RegionalDelegateAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\AdherentMessage\StatutoryAdherentMessage;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use App\Security\Voter\ScopeFeatureVoter;
use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const COMMITTEE = 'committee';
    public const SENATOR = 'senator';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const CANDIDATE = 'candidate';
    public const CANDIDATE_JECOUTE = 'candidate_jecoute';
    public const CORRESPONDENT = 'correspondent';
    public const REGIONAL_COORDINATOR = 'regional_coordinator';
    public const REGIONAL_DELEGATE = 'regional_delegate';
    public const PRESIDENT_DEPARTMENTAL_ASSEMBLY = 'president_departmental_assembly';
    public const STATUTORY = 'statutory';
    public const FDE_COORDINATOR = 'fde_coordinator';

    public const CLASSES = [
        self::DEPUTY => DeputyAdherentMessage::class,
        self::COMMITTEE => CommitteeAdherentMessage::class,
        self::SENATOR => SenatorAdherentMessage::class,
        self::LEGISLATIVE_CANDIDATE => LegislativeCandidateAdherentMessage::class,
        self::CANDIDATE => CandidateAdherentMessage::class,
        self::CANDIDATE_JECOUTE => CandidateJecouteMessage::class,
        self::CORRESPONDENT => CorrespondentAdherentMessage::class,
        self::REGIONAL_COORDINATOR => RegionalCoordinatorAdherentMessage::class,
        self::REGIONAL_DELEGATE => RegionalDelegateAdherentMessage::class,
        self::PRESIDENT_DEPARTMENTAL_ASSEMBLY => PresidentDepartmentalAssemblyAdherentMessage::class,
        self::STATUTORY => StatutoryAdherentMessage::class,
        self::FDE_COORDINATOR => FdeCoordinatorAdherentMessage::class,
    ];

    public const ROLES = [
        DeputyAdherentMessage::class => ['ROLE_DEPUTY', 'ROLE_DELEGATED_DEPUTY'],

        CommitteeAdherentMessage::class => ['ROLE_ANIMATOR', 'ROLE_DELEGATED_ANIMATOR'],

        SenatorAdherentMessage::class => ['ROLE_SENATOR', 'ROLE_DELEGATED_SENATOR'],

        LegislativeCandidateAdherentMessage::class => ['ROLE_LEGISLATIVE_CANDIDATE', 'ROLE_DELEGATED_LEGISLATIVE_CANDIDATE'],

        CandidateAdherentMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],
        CandidateJecouteMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],

        CorrespondentAdherentMessage::class => ['ROLE_CORRESPONDENT', 'ROLE_DELEGATED_CORRESPONDENT'],

        RegionalCoordinatorAdherentMessage::class => ['ROLE_REGIONAL_COORDINATOR', 'ROLE_DELEGATED_REGIONAL_COORDINATOR'],

        RegionalDelegateAdherentMessage::class => ['ROLE_REGIONAL_DELEGATE', 'ROLE_DELEGATED_REGIONAL_DELEGATE'],

        PresidentDepartmentalAssemblyAdherentMessage::class => ['ROLE_PRESIDENT_DEPARTMENTAL_ASSEMBLY', 'ROLE_DELEGATED_PRESIDENT_DEPARTMENTAL_ASSEMBLY'],

        StatutoryAdherentMessage::class => [[ScopeFeatureVoter::SCOPE_AND_FEATURE_GRANTED, [FeatureEnum::STATUTORY_MESSAGE]]],

        FdeCoordinatorAdherentMessage::class => ['ROLE_FDE_COORDINATOR', 'ROLE_DELEGATED_FDE_COORDINATOR'],
    ];

    public static function getMessageClassFromScopeCode(string $scopeCode, ?string $type): ?string
    {
        return self::CLASSES[static::getMessageTypeFromScopeCode($scopeCode, $type)] ?? null;
    }

    public static function getMessageTypeFromScopeCode(string $scopeCode, ?string $type = null): string
    {
        if (ScopeEnum::ANIMATOR === $scopeCode) {
            return self::COMMITTEE;
        }

        if (self::STATUTORY === $type) {
            return $type;
        }

        return $scopeCode;
    }
}
