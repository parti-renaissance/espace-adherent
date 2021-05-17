<?php

namespace App\Admin;

class Color
{
    public const BLACK = 'black';
    public const BLUE = 'blue';
    public const GREEN = 'green';
    public const PINK = 'pink';
    public const WHITE = 'white';
    public const YELLOW = 'yellow';

    public const ALL = [
        self::BLACK,
        self::BLUE,
        self::GREEN,
        self::PINK,
        self::WHITE,
        self::YELLOW,
    ];

    public const CHOICES = [
        'Blanc' => self::WHITE,
        'Bleu' => self::BLUE,
        'Jaune' => self::YELLOW,
        'Noir' => self::BLACK,
        'Rose' => self::PINK,
        'Vert' => self::GREEN,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }
}
