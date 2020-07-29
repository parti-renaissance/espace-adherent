<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\VotingPlatform\VoteResult\VoteResultAggregator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/resultats/{election_round_id}",
 *     name="app_voting_platform_results",
 *     methods={"GET"},
 *     defaults={"election_round_id": null}
 * )
 *
 * @ParamConverter("electionRound", options={"mapping": {"election_round_id": "id"}})
 */
class ResultsController extends AbstractController
{
    public function __invoke(
        VoteResultAggregator $resultAggregator,
        Election $election,
        ElectionRound $electionRound = null
    ): Response {
        if (!$election->isResultPeriodActive()) {
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
                : $election->getCurrentRound()
            ;
        }

        return $this->renderElectionTemplate('voting_platform/results.html.twig', $election, [
            'candidate_groups' => $this->candidateGroupRepository->findForElectionRound($electionRound),
            'results' => $resultAggregator->getResultsForRound($electionRound),
            'election_round' => $electionRound,
        ]);
    }
}
