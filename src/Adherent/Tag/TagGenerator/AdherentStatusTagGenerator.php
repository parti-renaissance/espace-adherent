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

        if (\count($adherent->getConfirmedPayments())) {
            return [
                TagEnum::ADHERENT,
                TagEnum::ADHERENT_COTISATION_OK,
            ];
        }

        if ($contributedAt = $adherent->getLastMembershipDonation()) {
            return [
                TagEnum::ADHERENT,
                $contributedAt->format('Y') === date('Y') ? TagEnum::ADHERENT_COTISATION_OK : TagEnum::ADHERENT_COTISATION_NOK,
            ];
        }

        return [
            TagEnum::SYMPATHISANT,
            $adherent->getActivatedAt() && $adherent->getActivatedAt() < new \DateTime('2022-09-17') ? TagEnum::SYMPATHISANT_COMPTE_EM : TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE,
        ];
    }
}
