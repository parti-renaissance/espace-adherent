<?php

namespace AppBundle\RepublicanSilence;

use AppBundle\Entity\RepublicanSilence;
use AppBundle\Repository\RepublicanSilenceRepository;

class Manager
{
    private $repository;

    public function __construct(RepublicanSilenceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return RepublicanSilence[]|iterable
     */
    public function getRepublicanSilenceForDate(\DateTime $date): iterable
    {
        return $this->repository->findStarted($date);
    }

    public function hasStartedSilence(array $userZones): bool
    {
        $silences = $this->getRepublicanSilenceForDate(new \DateTime());

        foreach ($silences as $silence) {
            if (array_intersect($silence->getZones(), $userZones)) {
                return true;
            }
        }

        return false;
    }
}
