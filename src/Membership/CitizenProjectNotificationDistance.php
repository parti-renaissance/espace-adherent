<?php

namespace AppBundle\Membership;

final class CitizenProjectNotificationDistance
{
    public const DISTANCE_2KM = 2;
    public const DISTANCE_5KM = 5;
    public const DISTANCE_10KM = 10;
    public const DISTANCE_20KM = 20;
    public const DISTANCE_50KM = 50;
    public const DISTANCE_100KM = 100;
    public const DISTANCE_ALL = 0;

    public const DISTANCES = [
        '2Km' => self::DISTANCE_2KM,
        '5Km' => self::DISTANCE_5KM,
        '10Km' => self::DISTANCE_10KM,
        '20Km' => self::DISTANCE_20KM,
        '50Km' => self::DISTANCE_50KM,
        '100Km' => self::DISTANCE_100KM,
        'Toutes' => self::DISTANCE_ALL,
    ];

    private function __construct()
    {
    }
}
