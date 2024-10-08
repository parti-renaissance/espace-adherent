<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Security\Voter\VotingPlatformAbleToVoteVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted(VotingPlatformAbleToVoteVoter::PERMISSION_RESULTS, subject: 'election')]
#[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
#[Route(path: '/resultats/{election_round_uuid}', name: 'app_voting_platform_results', defaults: ['election_round_uuid' => null], methods: ['GET'])]
class ResultsController extends AbstractController
{
    public function __invoke(
        VoteResultRepository $voteResultRepository,
        Election $election,
        ?ElectionRound $electionRound = null,
    ): Response {
        if (!$election->isResultsDisplayable() || !$election->isResultPeriodActive()) {
            return $this->redirectToRoute('vox_app');
        }

        if (
            $electionRound instanceof ElectionRound
            && (
                !$electionRound->isRoundOf($election)
                || ($election->isSecondRoundVotePeriodActive() && $electionRound->isActive())
            )
        ) {
            return $this->redirectToElectionRoute('app_voting_platform_results', $election);
        }

        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        return $this->renderElectionTemplate('voting_platform/results.html.twig', $election, [
            'vote_results' => $voteResultRepository->getResultsForRound($electionRound),
            'election_round' => $electionRound,
        ]);
    }
}
