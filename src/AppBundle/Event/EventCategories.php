<?php

namespace AppBundle\Event;

final class EventCategories
{
    const CHOICES = [
        'Kiosque' => 'CE001',
        'Réunion d\'équipe' => 'CE002',
        'Conférence-débat' => 'CE003',
        'Porte-à-porte' => 'CE004',
        'Atelier du programme' => 'CE005',
        'Tractage' => 'CE006',
        'Convivialité' => 'CE007',
        'Action ciblée' => 'CE008',
        'Événement innovant' => 'CE009',
        'Marche' => 'CE010',
        'Support party' => 'CE011',
    ];

    const ALL = [
        'CE001',
        'CE002',
        'CE003',
        'CE004',
        'CE005',
        'CE006',
        'CE007',
        'CE008',
        'CE009',
        'CE010',
        'CE011',
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }

    public static function getCategoryName($code)
    {
        return array_flip(self::CHOICES)[$code] ?? '';
    }
}
