<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeElectionExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_committee_candidacy_membership', [CommitteeRuntime::class, 'getCommitteeCandidacyMembership']),
            new TwigFunction('is_candidate', [CommitteeRuntime::class, 'isCandidate']),
        ];
    }
}
