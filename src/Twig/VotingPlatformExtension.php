<?php

namespace App\Twig;

use App\Entity\VotingPlatform\ElectionPool;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VotingPlatformExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_voting_platform_election_for_committee', [VotingPlatformRuntime::class, 'findElectionForCommittee']),
            new TwigFunction('get_voting_platform_election_for_territorial_council_election', [VotingPlatformRuntime::class, 'findElectionForTerritorialCouncilElection']),
            new TwigFunction('get_my_vote_for_election', [VotingPlatformRuntime::class, 'findMyVoteForElection']),
            new TwigFunction('get_election_last_vote', [VotingPlatformRuntime::class, 'findMyLastVote']),
            new TwigFunction('get_election_pool_title_key', [$this, 'getElectionPoolTitleKey']),
            new TwigFunction('get_election_participation_details', [VotingPlatformRuntime::class, 'getElectionParticipationDetails']),
            new TwigFunction('get_election_candidate_result', [VotingPlatformRuntime::class, 'getElectionCandidateResult']),
        ];
    }

    public function getElectionPoolTitleKey(ElectionPool $pool): string
    {
        $key = '';

        switch ($pool->getElection()->getDesignationType()) {
            case DesignationTypeEnum::COPOL:
                $key = 'territorial_council.membership.qualities.';
                break;
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                $key = 'common.';
                break;
        }

        return $key.$pool->getCode();
    }
}
