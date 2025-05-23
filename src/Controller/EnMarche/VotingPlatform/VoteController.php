<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Form\VotingPlatform\VotePoolCollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/vote', name: 'app_voting_platform_vote_step', methods: ['GET', 'POST'])]
class VoteController extends AbstractController
{
    public function __invoke(Request $request, Election $election): Response
    {
        // Get voteCommand from the session
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canVote($voteCommand)) {
            return $this->redirectToRoute('vox_app');
        }

        $this->processor->doVote($voteCommand);

        $pools = $election->getCurrentRound()->getElectionPools();

        $currentPool = null;
        if (($step = $request->query->getInt('s')) && isset($pools[$step - 1])) {
            $currentPool = $pools[$step - 1];
        }

        $currentPool = $voteCommand->updateForCurrentPool($pools, $currentPool);

        if ($request->query->has('back')) {
            // If `back` button was clicked, then need to redirect on the index page if 0 or 1 pool was already voted
            if (0 === array_search($currentPool, $pools)) {
                return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
            }

            $this->storage->save($voteCommand->removeLastChoice());

            return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
        }

        $form = $this
            ->createForm(
                VotePoolCollectionType::class,
                $voteCommand,
                [
                    'candidate_groups' => $candidateGroups = $currentPool->getCandidateGroups(),
                    'designation' => $election->getDesignation(),
                ]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $voteCommand->updatePoolChoice($currentPool);

            // If all candidates pools are finished (voted) we can redirect to the confirmation step
            if (\count($voteCommand->getChoicesByPools()) === \count($pools)) {
                $this->processor->doConfirm($voteCommand);

                return $this->redirectToElectionRoute('app_voting_platform_confirm_step', $election);
            }

            $this->storage->save($voteCommand);

            return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
        }

        return $this->renderElectionTemplate(
            'voting_platform/vote.html.twig',
            $election,
            [
                'form' => $form->createView(),
                'candidate_groups' => $candidateGroups,
                'pool' => $currentPool,
                'pool_index' => array_search($currentPool, $pools),
            ]
        );
    }
}
