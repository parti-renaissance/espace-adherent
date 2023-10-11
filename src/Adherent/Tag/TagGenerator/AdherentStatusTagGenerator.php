<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;

class AdherentStatusTagGenerator extends AbstractTagGenerator
{
    public function generate(Adherent $adherent): array
    {
        if (!$adherent->isRenaissanceUser()) {
            return [];
        }

        if ($adherent->isRenaissanceAdherent()) {
            return [
                TagEnum::ADHERENT,
                $adherent->hasActiveMembership() ? TagEnum::ADHERENT_COTISATION_OK : TagEnum::ADHERENT_COTISATION_NOK,
            ];
        }

        return [
            TagEnum::SYMPATHISANT,
            $adherent->getActivatedAt() && $adherent->getActivatedAt() < new \DateTime('2022-09-17') ? TagEnum::SYMPATHISANT_COMPTE_EM : TagEnum::SYMPATHISANT_COMPTE_RE,
        ];
    }
}
