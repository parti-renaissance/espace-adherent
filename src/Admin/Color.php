<?php

namespace AppBundle\Admin;

class Color
{
    const COLOR_BLACK = 'black';
    const COLOR_BLUE = 'blue';
    const COLOR_GREEN = 'green';
    const COLOR_PINK = 'pink';
    const COLOR_WHITE = 'white';
    const COLOR_YELLOW = 'yellow';

    const ALL = [
        self::COLOR_BLACK,
        self::COLOR_BLUE,
        self::COLOR_GREEN,
        self::COLOR_PINK,
        self::COLOR_WHITE,
        self::COLOR_YELLOW,
    ];

    const CHOICES = [
        'Blanc' => self::COLOR_WHITE,
        'Bleu' => self::COLOR_BLUE,
        'Jeune' => self::COLOR_YELLOW,
        'Noir' => self::COLOR_BLACK,
        'Rose' => self::COLOR_PINK,
        'Vert' => self::COLOR_GREEN,
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }
}
