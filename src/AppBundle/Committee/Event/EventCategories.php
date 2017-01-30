<?php

namespace AppBundle\Committee\Event;

final class EventCategories
{
    const CHOICES = [
        'Atelier de restitution du diagnostic' => 'CE001',
        'Réunion d\'équipe' => 'CE002',
        'Conférence-débat' => 'CE003',
        'Porte-à-porte' => 'CE004',
        'Atelier de réflexion' => 'CE005',
        'Action de terrain' => 'CE006',
        'Convivialité' => 'CE007',
        'Initiative citoyenne' => 'CE008',
        'Événement innovant' => 'CE009',
        'Atelier de plan de transformation' => 'CE010',
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
    ];

    private function __construct()
    {
    }

    public static function all()
    {
        return self::ALL;
    }
}
