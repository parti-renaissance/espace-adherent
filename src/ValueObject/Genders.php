<?php

namespace App\ValueObject;

final class Genders
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';
    public const UNKNOWN = 'unknown';

    public const ALL = [
        self::FEMALE,
        self::MALE,
        self::OTHER,
    ];

    public const MALE_FEMALE = [
        self::FEMALE,
        self::MALE,
    ];

    public const MALE_FEMALE_LABELS = [
        self::FEMALE => 'Femme',
        self::MALE => 'Homme',
    ];

    public const CHOICES = [
        'common.gender.woman' => self::FEMALE,
        'common.gender.man' => self::MALE,
        'common.gender.other' => self::OTHER,
    ];

    public const CHOICES_LABELS = [
        self::FEMALE => 'Femme',
        self::MALE => 'Homme',
        self::OTHER => 'Autre',
    ];

    public const CIVILITY_CHOICES = [
        'common.civility.mrs' => self::FEMALE,
        'common.civility.mr' => self::MALE,
    ];

    private function __construct()
    {
    }

    public static function all(): array
    {
        return self::ALL;
    }
}
