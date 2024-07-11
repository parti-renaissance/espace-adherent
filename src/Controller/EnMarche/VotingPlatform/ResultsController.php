<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Security\Voter\VotingPlatform\AbleToVoteVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted(AbleToVoteVoter::PERMISSION_RESULTS, subject: 'election')]
#[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
#[Route(path: '/resultats/{election_round_uuid}', name: 'app_voting_platform_results', methods: ['GET'], defaults: ['election_round_uuid' => null])]
class ResultsController extends AbstractController
{
    public function __invoke(
        VoteResultRepository $voteResultRepository,
        Election $election,
        Request $request,
        ?ElectionRound $electionRound = null
    ): Response {
        if (!$election->isResultsDisplayable() || !$election->isResultPeriodActive()) {
            return $this->redirect($this->redirectManager->getRedirection($election));
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

        return $this->renderElectionTemplate('voting_platform/results.html.twig', $election, $request, [
            'vote_results' => $voteResultRepository->getResultsForRound($electionRound),
            'election_round' => $electionRound,
        ]);
    }
}
