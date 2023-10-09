<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;

class AdherentStatusTagGenerator extends AbstractTagGenerator
{
    public function generate(Adherent $adherent): ?string
    {
        if (!$adherent->isRenaissanceUser()) {
            return null;
        }

        if ($adherent->isRenaissanceAdherent()) {
            if ($adherent->hasActiveMembership()) {
                return TagEnum::ADHERENT_COTISATION_OK;
            }

            return TagEnum::ADHERENT_COTISATION_NOK;
        }

        if ($adherent->getActivatedAt() && $adherent->getActivatedAt() < new \DateTime('2022-09-17')) {
            return TagEnum::SYMPATHISANT_COMPTE_EM;
        }

        return TagEnum::SYMPATHISANT_COMPTE_RE;
    }
}
