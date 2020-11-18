<?php

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\LegislativeCandidateAdherentMessage;
use App\Entity\AdherentMessage\LreManagerElectedRepresentativeMessage;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\ReferentElectedRepresentativeMessage;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use MyCLabs\Enum\Enum;

class AdherentMessageTypeEnum extends Enum
{
    public const DEPUTY = 'deputy';
    public const REFERENT = 'referent';
    public const COMMITTEE = 'committee';
    public const CITIZEN_PROJECT = 'citizen_project';
    public const MUNICIPAL_CHIEF = 'municipal_chief';
    public const SENATOR = 'senator';
    public const REFERENT_ELECTED_REPRESENTATIVE = 'referent_elected_representative';
    public const LRE_MANAGER_ELECTED_REPRESENTATIVE = 'lre_manager_elected_representative';
    public const REFERENT_INSTANCES = 'referent_instances';
    public const LEGISLATIVE_CANDIDATE = 'legislative_candidate';
    public const CANDIDATE = 'candidate';

    public const CLASSES = [
        self::DEPUTY => DeputyAdherentMessage::class,
        self::REFERENT => ReferentAdherentMessage::class,
        self::COMMITTEE => CommitteeAdherentMessage::class,
        self::CITIZEN_PROJECT => CitizenProjectAdherentMessage::class,
        self::MUNICIPAL_CHIEF => MunicipalChiefAdherentMessage::class,
        self::SENATOR => SenatorAdherentMessage::class,
        self::REFERENT_ELECTED_REPRESENTATIVE => ReferentElectedRepresentativeMessage::class,
        self::LRE_MANAGER_ELECTED_REPRESENTATIVE => LreManagerElectedRepresentativeMessage::class,
        self::REFERENT_INSTANCES => ReferentInstancesMessage::class,
        self::LEGISLATIVE_CANDIDATE => LegislativeCandidateAdherentMessage::class,
        self::CANDIDATE => CandidateAdherentMessage::class,
    ];
}
