<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VotingPlatformExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_voting_platform_election_for_committee', [VotingPlatformRuntime::class, 'findElectionForCommittee']),
        ];
    }
}
