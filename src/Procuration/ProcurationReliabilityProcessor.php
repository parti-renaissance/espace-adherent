<?php

namespace AppBundle\Procuration;

use AppBundle\Entity\Adherent;
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
            || $adherent->isMunicipalChief()
        ) {
            $proxy->setRepresentativeReliability();

            return;
        }

        if (
            $adherent->isHost()
            || $adherent->isCitizenProjectAdministrator()
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
