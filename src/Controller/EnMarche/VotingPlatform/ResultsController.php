<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\VoteResultRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/resultats", name="app_voting_platform_results", methods={"GET"})
 */
class ResultsController extends AbstractController
{
    /** @var VoteResultRepository */
    private $voteResultRepository;

    /** @required */
    public function setVoteResultRepository(VoteResultRepository $voteResultRepository): void
    {
        $this->voteResultRepository = $voteResultRepository;
    }

    public function __invoke(Election $election): Response
    {
        if (!$election->isResultPeriodActive()) {
            return $this->redirect($this->redirectManager->getRedirection($election));
        }

        return $this->renderElectionTemplate(
            sprintf('voting_platform/results/%s.html.twig', $election->getDesignationType()),
            $election,
            [
                'candidate_groups' => $this->candidateGroupRepository->findWithResultsForElection($election),
                'results' => $this->voteResultRepository->getResults($election),
            ]
        );
    }
}
