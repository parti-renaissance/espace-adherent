<?php

namespace AppBundle\ValueObject;

final class Genders
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';

    public const ALL = [
        self::FEMALE,
        self::MALE,
        self::OTHER,
    ];

    public const CHOICES = [
        'common.gender.woman' => self::FEMALE,
        'common.gender.man' => self::MALE,
        'common.gender.other' => self::OTHER,
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
