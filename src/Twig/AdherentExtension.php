<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('member_interest_label', [AdherentRuntime::class, 'getMemberInterestLabel']),
            new TwigFunction('get_user_level_label', [AdherentRuntime::class, 'getUserLevelLabel']),
            new TwigFunction('get_adherent_role_labels', [AdherentRuntime::class, 'getAdherentRoleLabels']),
            new TwigFunction('get_referent_previous_visit_date', [AdherentRuntime::class, 'getReferentPreviousVisitDate']),
        ];
    }
}
