<?php

namespace App\Scope\Generator;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\Geo\ZoneRepository;
use App\Repository\ScopeRepository;
use App\Scope\ScopeEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class PapNationalManagerScopeGenerator extends AbstractScopeGenerator
{
    public function __construct(
        ScopeRepository $scopeRepository,
        TranslatorInterface $translator,
        private readonly ZoneRepository $zoneRepository,
    ) {
        parent::__construct($scopeRepository, $translator);
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
            'code' => AddressInterface::FRANCE,
        ])];
    }
}
