<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\ReportRepository;

class UpdateReportHandler implements UnregistrationAdherentHandlerInterface
{
    private $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->repository->anonymizeAuthorReports($adherent);
    }
}
