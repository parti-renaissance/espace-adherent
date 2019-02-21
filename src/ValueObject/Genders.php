<?php

namespace AppBundle\ValueObject;

final class Genders
{
    public const MALE = 'male';
    public const FEMALE = 'female';
    public const OTHER = 'other';

    public const ALL = [
        self::MALE,
        self::FEMALE,
        self::OTHER,
    ];

    public const CHOICES = [
        'common.gender.man' => self::MALE,
        'common.gender.woman' => self::FEMALE,
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
