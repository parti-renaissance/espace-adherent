<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Security\Voter\VotingPlatformAbleToVoteVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(VotingPlatformAbleToVoteVoter::PERMISSION_RESULTS, subject: 'election')]
#[Route(path: '/resultats/{election_round_uuid}', name: 'app_voting_platform_results', defaults: ['election_round_uuid' => null], methods: ['GET'])]
class ResultsController extends AbstractController
{
    public function __invoke(
        Election $election,
        #[MapEntity(mapping: ['election_round_uuid' => 'uuid'])]
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
            'vote_results' => [],
            'election_round' => $electionRound,
        ]);
    }
}
