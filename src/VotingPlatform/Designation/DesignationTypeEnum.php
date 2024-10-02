<?php

namespace App\VotingPlatform\Designation;

use MyCLabs\Enum\Enum;

final class DesignationTypeEnum extends Enum
{
    public const COMMITTEE_ADHERENT = 'committee_adherent';
    public const COMMITTEE_ADHERENT_FEMALE = 'committee_adherent_female';
    public const COMMITTEE_ADHERENT_MALE = 'committee_adherent_male';

    public const COMMITTEE_SUPERVISOR = 'committee_supervisor';
    public const COMMITTEE_SUPERVISOR_FEMALE = 'committee_supervisor_female';
    public const COMMITTEE_SUPERVISOR_MALE = 'committee_supervisor_male';

    public const COPOL = 'copol';
    public const NATIONAL_COUNCIL = 'national_council';
    public const EXECUTIVE_OFFICE = 'executive_office';

    public const POLL = 'poll';
    public const LOCAL_POLL = 'local_poll';
    public const LOCAL_ELECTION = 'local_election';

    public const CONSULTATION = 'consultation';
    public const TERRITORIAL_ASSEMBLY = 'territorial_assembly';

    public const TITLES = [
        self::COMMITTEE_SUPERVISOR => 'Élection du binôme paritaire d’Animateurs locaux',
        self::COMMITTEE_SUPERVISOR_FEMALE => 'Élection de l’Animatrice locale du comité',
        self::COMMITTEE_SUPERVISOR_MALE => 'Élection de l’Animateur local du comité',
        self::COMMITTEE_ADHERENT => 'Désignation du binôme d’adhérents siégeant au Conseil territorial',
        self::COMMITTEE_ADHERENT_FEMALE => 'Désignation d’une adhérente siégeant au Conseil territorial',
        self::COMMITTEE_ADHERENT_MALE => 'Désignation d’un adhérent siégeant au Conseil territorial',
        self::COPOL => 'Désignation des binômes au Copol',
        self::NATIONAL_COUNCIL => 'Désignation des membres siégeant au Conseil national',
        self::EXECUTIVE_OFFICE => 'Élection des membres du Bureau Exécutif',
        self::POLL => 'Convocation de la Convention de La République En Marche !',
        self::LOCAL_ELECTION => 'Élection départementale',
        self::LOCAL_POLL => 'Élection locale',
        self::CONSULTATION => 'Consultation nationale',
        self::TERRITORIAL_ASSEMBLY => 'Élection du Bureau de l’Assemblée des territoires',
    ];

    public const MAIN_TYPES = [
        self::COMMITTEE_SUPERVISOR,
        self::COMMITTEE_ADHERENT,
        self::COPOL,
        self::NATIONAL_COUNCIL,
        self::EXECUTIVE_OFFICE,
        self::POLL,
        self::LOCAL_ELECTION,
        self::LOCAL_POLL,
        self::CONSULTATION,
        self::TERRITORIAL_ASSEMBLY,
    ];

    public const RENAISSANCE_TYPES = [
        self::CONSULTATION,
        self::LOCAL_ELECTION,
        self::COMMITTEE_SUPERVISOR,
        self::TERRITORIAL_ASSEMBLY,
    ];

    public const NATIONAL_TYPES = [
        self::POLL,
        self::CONSULTATION,
        self::TERRITORIAL_ASSEMBLY,
    ];

    public const API_AVAILABLE_TYPES = [
        self::COMMITTEE_SUPERVISOR,
        self::CONSULTATION,
    ];
}
