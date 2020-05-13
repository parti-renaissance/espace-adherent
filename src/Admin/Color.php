<?php

namespace App\Admin;

class Color
{
    const BLACK = 'black';
    const BLUE = 'blue';
    const GREEN = 'green';
    const PINK = 'pink';
    const WHITE = 'white';
    const YELLOW = 'yellow';

    const ALL = [
        self::BLACK,
        self::BLUE,
        self::GREEN,
        self::PINK,
        self::WHITE,
        self::YELLOW,
    ];

    const CHOICES = [
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
