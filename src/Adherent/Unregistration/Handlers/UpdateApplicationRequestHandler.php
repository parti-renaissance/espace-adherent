<?php

namespace App\Adherent\Unregistration\Handlers;

use App\Entity\Adherent;
use App\Repository\ApplicationRequest\RunningMateRequestRepository;
use App\Repository\ApplicationRequest\VolunteerRequestRepository;

class UpdateApplicationRequestHandler implements UnregistrationAdherentHandlerInterface
{
    private $volunteerRequestRepository;
    private $runningMateRequestRepository;

    public function __construct(
        VolunteerRequestRepository $volunteerRequestRepository,
        RunningMateRequestRepository $runningMateRequestRepository
    ) {
        $this->volunteerRequestRepository = $volunteerRequestRepository;
        $this->runningMateRequestRepository = $runningMateRequestRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $this->volunteerRequestRepository->updateAdherentRelation($adherent->getEmailAddress(), null);
        $this->runningMateRequestRepository->updateAdherentRelation($adherent->getEmailAddress(), null);
    }
}
