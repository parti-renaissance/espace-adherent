<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use App\Repository\ScopeRepository;
use App\Scope\ScopeEnum;

class PapNationalManagerScopeGenerator extends AbstractScopeGenerator
{
    private ZoneRepository $zoneRepository;

    public function __construct(ScopeRepository $scopeRepository, ZoneRepository $zoneRepository)
    {
        parent::__construct($scopeRepository);

        $this->zoneRepository = $zoneRepository;
    }

    public function getCode(): string
    {
        return ScopeEnum::PAP_NATIONAL_MANAGER;
    }

    public function supports(Adherent $adherent): bool
    {
        return $adherent->hasPapNationalManagerRole();
    }

    public function getZones(Adherent $adherent): array
    {
        return [$this->zoneRepository->findOneBy([
            'type' => Zone::COUNTRY,
            'code' => 'FR',
        ])];
    }
}
