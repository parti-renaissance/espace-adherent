<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Form\ConfirmActionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/confirmer", name="app_voting_platform_confirm_step", methods={"GET", "POST"})
 */
class ConfirmController extends AbstractController
{
    public function __invoke(Request $request, Election $election): Response
    {
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canConfirm($voteCommand)) {
            return $this->redirectToElectionRoute('app_voting_platform_vote_step', $election);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->remove('deny')
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doFinish($voteCommand);

            return $this->redirectToElectionRoute('app_voting_platform_finish_step', $election);
        }

        return $this->renderElectionTemplate('voting_platform/confirmation.html.twig', $election, [
            'form' => $form->createView(),
            'vote_command' => $voteCommand,
            'candidate_groups' => $this->candidateGroupRepository->findByUuids($voteCommand->getCandidateGroupUuids()),
        ]);
    }
}
