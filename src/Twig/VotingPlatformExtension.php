<?php

declare(strict_types=1);

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
            new TwigFunction('get_voting_platform_election_for_designation', [VotingPlatformRuntime::class, 'findElectionForDesignation']),
            new TwigFunction('get_my_vote_for_election', [VotingPlatformRuntime::class, 'findMyVoteForElection']),
            new TwigFunction('has_voted_for_designation', [VotingPlatformRuntime::class, 'hasVotedForDesignation']),
            new TwigFunction('get_election_pool_title_key', [$this, 'getElectionPoolTitleKey']),
            new TwigFunction('get_election_participation_details', [VotingPlatformRuntime::class, 'getElectionParticipationDetails']),
            new TwigFunction('aggregate_pool_results', [VotingPlatformRuntime::class, 'aggregatePoolResults']),
            new TwigFunction('get_election_candidate_result', [VotingPlatformRuntime::class, 'getElectionCandidateResult']),
            new TwigFunction('find_committee_for_recent_candidate', [VotingPlatformRuntime::class, 'findCommitteeForRecentCandidate']),
            new TwigFunction('find_committee_for_recent_vote', [VotingPlatformRuntime::class, 'findCommitteeForRecentVote']),
            new TwigFunction('find_active_designations', [VotingPlatformRuntime::class, 'findActiveDesignations']),
            new TwigFunction('get_election_stats', [VotingPlatformRuntime::class, 'getElectionStats']),
        ];
    }

    public function getElectionPoolTitleKey(ElectionPool $pool): string
    {
        $key = 'voting_platform.pool_title.';

        switch ($pool->getElection()->getDesignationType()) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
                $key = 'common.';
                break;
            case DesignationTypeEnum::LOCAL_POLL:
            case DesignationTypeEnum::CONSULTATION:
            case DesignationTypeEnum::VOTE:
            case DesignationTypeEnum::TERRITORIAL_ASSEMBLY:
            case DesignationTypeEnum::CONGRESS_CN:
                $key = '';
                break;
        }

        return $key.$pool->getCode();
    }
}
