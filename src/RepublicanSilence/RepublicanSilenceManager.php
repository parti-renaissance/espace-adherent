<?php

namespace App\RepublicanSilence;

use App\Entity\RepublicanSilence;
use App\Repository\Geo\ZoneRepository;
use App\Repository\RepublicanSilenceRepository;

class RepublicanSilenceManager
{
    private RepublicanSilenceRepository $repository;
    private ZoneRepository $zoneRepository;

    public function __construct(RepublicanSilenceRepository $repository, ZoneRepository $zoneRepository)
    {
        $this->repository = $repository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesForDate(\DateTimeInterface $date): iterable
    {
        return $this->repository->findStarted($date);
    }

    /**
     * @return RepublicanSilence[]
     */
    public function getRepublicanSilencesFromDate(\DateTimeInterface $date): iterable
    {
        return $this->repository->findFromDate($date);
    }

    public function hasStartedSilence(?array $zones = null): bool
    {
        $silences = $this->getRepublicanSilencesForDate(new \DateTime());

        if (null === $zones) {
            return !empty($silences);
        }

        foreach ($silences as $silence) {
            if ($this->matchSilence($silence, $zones)) {
                return true;
            }
        }

        return false;
    }

    private function matchSilence(RepublicanSilence $silence, array $zones): bool
    {
        return $this->zoneRepository->isInZones($zones, $silence->getZones()->toArray());
    }
}
