<?php

namespace App\LocalElection;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\LocalElection\LocalElection;
use App\Repository\LocalElection\LocalElectionRepository;

class Manager
{
    public function __construct(private readonly LocalElectionRepository $localElectionRepository)
    {
    }

    public function getLastLocalElection(Adherent $adherent): ?LocalElection
    {
        $zones = $adherent->getZonesOfType(Zone::DEPARTMENT, true);

        return $zones ? $this->localElectionRepository->findLastForZones($zones) : null;
    }
}
