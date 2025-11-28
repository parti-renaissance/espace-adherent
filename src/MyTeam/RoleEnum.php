<?php

declare(strict_types=1);

namespace App\MyTeam;

use MyCLabs\Enum\Enum;

class RoleEnum extends Enum
{
    public const GENERAL_SECRETARY = 'general_secretary';
    public const TREASURER = 'treasurer';
    public const MOBILIZATION_MANAGER = 'mobilization_manager';
    public const LOGISTICS_MANAGER = 'logistics_manager';
    public const COMMUNICATION_MANAGER = 'communication_manager';
    public const COMPLIANCE_AND_FINANCE_MANAGER = 'compliance_and_finance_manager';
    public const HEAD_OF_EUROPEAN_AFFAIRS = 'head_of_european_affairs';
    public const HEAD_OF_GENDER_EQUALITY = 'head_of_gender_equality';
    public const HEAD_OF_RELATIONS_AND_TRAINING_OF_ELECTED_OFFICIALS = 'head_of_relations_and_training_of_elected_officials';
    public const NEW_MEMBERS_MANAGER = 'new_members_manager';
    public const TRAINING_MANAGER = 'training_manager';
    public const MANAGER = 'manager';

    public const LABELS = [
        self::GENERAL_SECRETARY => 'Secrétaire général',
        self::TREASURER => 'Trésorier',
        self::MOBILIZATION_MANAGER => 'Responsable mobilisation',
        self::LOGISTICS_MANAGER => 'Responsable logistique',
        self::COMMUNICATION_MANAGER => 'Responsable communication',
        self::COMPLIANCE_AND_FINANCE_MANAGER => 'Responsables conformité et finance',
        self::HEAD_OF_GENDER_EQUALITY => 'Responsable Égalité Femme / Homme',
        self::HEAD_OF_EUROPEAN_AFFAIRS => 'Responsable des questions européennes',
        self::HEAD_OF_RELATIONS_AND_TRAINING_OF_ELECTED_OFFICIALS => 'Responsable des relations et formation élus',
        self::NEW_MEMBERS_MANAGER => 'Responsable nouveaux adhérents',
        self::TRAINING_MANAGER => 'Responsable formation',
        self::MANAGER => 'Responsable',
    ];
}
