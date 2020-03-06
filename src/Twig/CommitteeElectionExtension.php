<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommitteeElectionExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_voting_committee_name', [CommitteeRuntime::class, 'getVotingCommitteeName']),
            new TwigFunction('get_candidacy_committee_name', [CommitteeRuntime::class, 'getCandidacyCommitteeName']),
            new TwigFunction('is_candidate', [CommitteeRuntime::class, 'isCandidate']),
        ];
    }
}
