<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;

class AdherentElectStatusTagGenerator extends AbstractTagGenerator
{
    public function generate(Adherent $adherent, array $previousTags): array
    {
        if (!$adherent->findElectedRepresentativeMandates(true)) {
            return [];
        }

        $tags = [];

        $countPayments = \count($adherent->getConfirmedPayments());

        if (null === $adherent->getLastRevenueDeclaration() && 0 === $countPayments) {
            $tags[] = TagEnum::ELU_ATTENTE_DECLARATION;
        } elseif (null === $adherent->getContributionAmount()) {
            $tags[] = TagEnum::ELU_COTISATION_OK_NON_SOUMIS;
        } elseif ($countPayments) {
            $tags[] = TagEnum::ELU_COTISATION_OK_SOUMIS;
        } else {
            $tags[] = TagEnum::ELU_COTISATION_NOK;
        }

        if ($adherent->exemptFromCotisation) {
            if (TagEnum::includesTag(TagEnum::getAdherentYearTag(date('Y')), $previousTags)) {
                $tags[] = TagEnum::ELU_COTISATION_OK_EXEMPTE;
            } else {
                $tags[] = TagEnum::ELU_EXEMPTE_ET_ADHERENT_COTISATION_NOK;
            }
        }

        return $tags;
    }
}
