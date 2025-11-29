<?php

declare(strict_types=1);

namespace App\JMEFilter\FilterGroup;

class PersonalInformationsFilterGroup extends AbstractFilterGroup
{
    protected const LABEL = 'Informations personnelles';
    protected const COLOR = '#0E7490';

    public function getPosition(): int
    {
        return 1;
    }
}
