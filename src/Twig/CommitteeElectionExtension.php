<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeElectionExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('is_voting_committee', [CommitteeRuntime::class, 'isVotingCommittee']),
            new TwigFunction('is_candidate', [CommitteeRuntime::class, 'isCandidate']),
        ];
    }
}
