<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TerritorialCouncilExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_election_poll_vote', [TerritorialCouncilRuntime::class, 'getElectionPollVote']),
        ];
    }
}
