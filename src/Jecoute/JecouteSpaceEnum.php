<?php

namespace App\Jecoute;

use App\Scope\ScopeEnum;
use MyCLabs\Enum\Enum;

class JecouteSpaceEnum extends Enum
{
    public const REFERENT_SPACE = 'referent';
    public const MANAGER_SPACE = 'manager';
    public const MUNICIPAL_CHIEF_SPACE = 'municipal_chief';
    public const CANDIDATE_SPACE = 'candidate';
    public const CORRESPONDENT_SPACE = 'correspondent';

    public static function getLabels(): array
    {
        return [
            self::REFERENT_SPACE => 'référent',
            self::MANAGER_SPACE => 'responsable des questionnaires',
            self::MUNICIPAL_CHIEF_SPACE => 'candidat Municipales',
            self::CANDIDATE_SPACE => 'candidat aux départementales',
            self::CORRESPONDENT_SPACE => 'correspondant',
        ];
    }

    public static function getByScope(string $scope): ?string
    {
        switch ($scope) {
            case ScopeEnum::REFERENT:
                return self::REFERENT_SPACE;
            case ScopeEnum::CORRESPONDENT:
                return self::CORRESPONDENT_SPACE;
            default:
                return null;
        }
    }

    public static function getLabel(string $space): ?string
    {
        return static::isValid($space) ? static::getLabels()[$space] : null;
    }
}
