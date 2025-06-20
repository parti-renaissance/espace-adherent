<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdherentExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_adherent_role_labels', [AdherentRuntime::class, 'getAdherentRoleLabels']),
            new TwigFunction('get_elected_representative', [AdherentRuntime::class, 'getElectedRepresentative']),
            new TwigFunction('has_active_parliamentary_mandate', [AdherentRuntime::class, 'hasActiveParliamentaryMandate']),
            new TwigFunction('get_session_modal_context', [AdherentRuntime::class, 'getSessionModalContext']),
            new TwigFunction('translate_tag', [AdherentRuntime::class, 'translateTag']),
            new TwigFunction('count_contribution', [AdherentRuntime::class, 'countContribution']),
            new TwigFunction('get_adherent_by_uuid', [AdherentRuntime::class, 'getAdherentByUuid']),
            new TwigFunction('get_committee_by_uuid', [AdherentRuntime::class, 'getCommitteeByUuid']),
        ];
    }
}
