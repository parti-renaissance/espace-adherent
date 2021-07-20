<?php

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CandidateJecouteMessage;
use App\Entity\AdherentMessage\CoalitionsMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\LreManagerElectedRepresentativeMessage;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\Audience\CandidateAudience;
use App\Entity\Audience\DeputyAudience;
use App\Entity\Audience\ReferentAudience;
use App\Entity\Audience\SenatorAudience;
use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const COMMITTEE = 'committee';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const SENATOR = 'senator';
    public const REFERENT_ELECTED_REPRESENTATIVE = 'referent_elected_representative';
    public const LRE_MANAGER_ELECTED_REPRESENTATIVE = 'lre_manager_elected_representative';
    public const REFERENT_INSTANCES = 'referent_instances';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const CANDIDATE = 'candidate';
    public const CANDIDATE_JECOUTE = 'candidate_jecoute';
    public const COALITIONS = 'coalitions';

    public const CLASSES = [
        self::DEPUTY => DeputyAdherentMessage::class,
        self::REFERENT => ReferentAdherentMessage::class,
        self::COMMITTEE => CommitteeAdherentMessage::class,
        self::MUNICIPAL_CHIEF => MunicipalChiefAdherentMessage::class,
        self::SENATOR => SenatorAdherentMessage::class,
        self::REFERENT_ELECTED_REPRESENTATIVE => ReferentElectedRepresentativeMessage::class,
        self::LRE_MANAGER_ELECTED_REPRESENTATIVE => LreManagerElectedRepresentativeMessage::class,
        self::REFERENT_INSTANCES => ReferentInstancesMessage::class,
        self::LEGISLATIVE_CANDIDATE => LegislativeCandidateAdherentMessage::class,
        self::CANDIDATE => CandidateAdherentMessage::class,
        self::CANDIDATE_JECOUTE => CandidateJecouteMessage::class,
        self::COALITIONS => CoalitionsMessage::class,
    ];

    public const AUDIENCE_CLASSES = [
        self::DEPUTY => DeputyAudience::class,
        self::REFERENT => ReferentAudience::class,
        self::SENATOR => SenatorAudience::class,
        self::CANDIDATE => CandidateAudience::class,
    ];

    public const ROLES = [
        DeputyAdherentMessage::class => ['ROLE_DEPUTY', 'ROLE_DELEGATED_DEPUTY'],

        CommitteeAdherentMessage::class => 'ROLE_HOST',

        MunicipalChiefAdherentMessage::class => 'ROLE_MUNICIPAL_CHIEF',

        SenatorAdherentMessage::class => ['ROLE_SENATOR', 'ROLE_DELEGATED_SENATOR'],

        LreManagerElectedRepresentativeMessage::class => 'ROLE_LRE',

        ReferentAdherentMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],
        ReferentElectedRepresentativeMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],
        ReferentInstancesMessage::class => ['ROLE_REFERENT', 'ROLE_DELEGATED_REFERENT'],

        LegislativeCandidateAdherentMessage::class => 'ROLE_LEGISLATIVE_CANDIDATE',

        CandidateAdherentMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],
        CandidateJecouteMessage::class => ['ROLE_CANDIDATE', 'ROLE_DELEGATED_CANDIDATE'],

        CoalitionsMessage::class => 'ROLE_CAUSE_AUTHOR',
    ];
}
