<?php

namespace App\Procuration;

use App\Entity\Adherent;
use App\Entity\ProcurationProxy;
use App\Repository\AdherentRepository;

class ProcurationReliabilityProcessor
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function process(ProcurationProxy $proxy): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($proxy->getEmailAddress());

        if (!$adherent instanceof Adherent || !$adherent->isAdherent()) {
            return;
        }

        if (
            $adherent->isReferent()
            || $adherent->isCoReferent()
            || $adherent->isSenator()
            || $adherent->isDeputy()
            || $adherent->isCoordinatorCommitteeSector()
        ) {
            $proxy->setRepresentativeReliability();

            return;
        }

        if (
            $adherent->isHost()
            || $adherent->isSupervisor()
            || $adherent->isJecouteManager()
            || $adherent->isAssessorManager()
            || $adherent->isProcurationsManager()
        ) {
            $proxy->setActivistReliability();

            return;
        }

        $proxy->setAdherentReliability();
    }
}
