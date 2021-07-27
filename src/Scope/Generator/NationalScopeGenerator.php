<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;

class NationalScopeGenerator extends AbstractScopeGenerator
{
    private $zoneRepository;

    public function __construct(ZoneRepository $zoneRepository)
    {
        $this->zoneRepository = $zoneRepository;
    }

    public function getScope(): string
    {
        return ScopeEnum::NATIONAL;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasNationalRole();
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$this->zoneRepository->findOneBy([
            'type' => Zone::COUNTRY,
            'code' => 'FR',
        ])];
    }
}
