<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;

class AdherentElectStatusTagGenerator extends AbstractTagGenerator
{
    public function generate(Adherent $adherent): array
    {
        if (!$adherent->findElectedRepresentativeMandates(true)) {
            return [];
        }

        $tags = [TagEnum::ELU];

        if ($adherent->getLastRevenueDeclaration()) {
            $tags[] = TagEnum::ELU_DECLARATION_OK;
        }

        if ($adherent->getContributionAmount()) {
            $tags[] = TagEnum::ELU_COTISATION_ELIGIBLE;
        }

        if ($adherent->getConfirmedPayments()) {
            $tags[] = TagEnum::ELU_COTISATION_OK;
        }

        if ($adherent->exemptFromCotisation) {
            $tags[] = TagEnum::ELU_EXEMPTE;
        }

        return $tags;
    }
}
