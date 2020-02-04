<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Repository\AdherentRepository;

class ProcurationReliabilityProcessor
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public function process(ProcurationProxy $proxy): void
    {
        if (!$adherent = $this->adherentRepository->findOneByEmail($proxy->getEmailAddress())) {
            return;
        }

        if (
            $adherent->isReferent()
            || $adherent->isCoReferent()
            || $adherent->isSenator()
            || $adherent->isDeputy()
            || $adherent->isCoordinator()
            || $adherent->isMunicipalChief()
        ) {
            $proxy->setRepresentativeReliability();

            return;
        }

        if (
            $adherent->isHost()
            || $adherent->isCitizenProjectAdministrator()
            || $adherent->isCoordinatorCommitteeSector()
            || $adherent->isCoordinatorCitizenProjectSector()
            || $adherent->isJecouteManager()
            || $adherent->isAssessorManager()
            || $adherent->isProcurationManager()
        ) {
            $proxy->setActivistReliability();

            return;
        }

        $proxy->setAdherentReliability();
    }
}
