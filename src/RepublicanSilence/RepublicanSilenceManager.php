<?php

namespace App\RepublicanSilence;

use App\Entity\RepublicanSilence;
use App\Repository\RepublicanSilenceRepository;

class RepublicanSilenceManager
{
    public function __construct(private readonly RepublicanSilenceRepository $repository)
    {
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
}
