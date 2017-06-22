<?php

namespace AppBundle\Summary;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Summary;
use AppBundle\Repository\SummaryRepository;

class SummaryManager
{
    private $factory;
    private $repository;

    public function __construct(SummaryFactory $factory, SummaryRepository $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    public function getForAdherent(Adherent $adherent): Summary
    {
        if ($summary = $this->repository->findOneForAdherent($adherent)) {
            return $summary;
        }

        return $this->factory->createFromAdherent($adherent);
    }
}
