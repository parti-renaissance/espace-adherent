<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\ReferentTagRepository;

class ReferentTagManager
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    public function assignAdherentLocalTag(Adherent $adherent): void
    {
        $adherent->removeReferentTags();

        $code = ManagedAreaUtils::getCodeFromAdherent($adherent);

        if (!$tag = $this->referentTagRepository->findOneByCode($code)) {
            return;
        }

        $adherent->addReferentTag($tag);
    }
}
