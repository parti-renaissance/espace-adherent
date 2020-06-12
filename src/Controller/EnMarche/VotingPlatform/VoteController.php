<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Form\VotingPlatform\VotePoolCollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/vote", name="app_voting_platform_vote_step", methods={"GET", "POST"})
 */
class VoteController extends AbstractController
{
    public function __invoke(Request $request, Election $election): Response
    {
        // Get voteCommand from the session
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canVote($voteCommand)) {
            return $this->redirectToElectionRoute('app_voting_platform_index', $election);
        }

        $this->processor->doVote($voteCommand);

        $pools = $election->getCurrentRound()->getElectionPools();
        $currentPool = $voteCommand->updateForCurrentPool($pools);

        $form = $this
            ->createForm(
                VotePoolCollectionType::class,
                $voteCommand,
                ['candidate_groups' => $candidateGroups = $currentPool->getCandidateGroups()]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted()) {
            if ($form->get('back')->isClicked()) {
                // If `back` button was clicked, then need to redirect on the index page if 0 or 1 pool was already voted
                if (\count($voteCommand->getChoicesByPools()) < 1) {
                    return $this->redirectToElectionRoute('app_voting_platform_index', $election);
                }

                $this->storage->save($voteCommand->removeLastChoice());

                return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
            }

            if ($form->isValid()) {
                $voteCommand->updatePoolChoice($currentPool);

                // If all candidates pools are finished (voted) we can redirect to the confirm step
                if (\count($voteCommand->getChoicesByPools()) === \count($pools)) {
                    $this->processor->doConfirm($voteCommand);

                    return $this->redirectToElectionRoute('app_voting_platform_confirm_step', $election);
                }

                $this->storage->save($voteCommand);

                return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
            }
        }

        return $this->renderElectionTemplate(
            'voting_platform/vote.html.twig',
            $election,
            [
                'form' => $form->createView(),
                'candidate_groups' => $candidateGroups,
                'pool' => $currentPool,
            ]
        );
    }
}
