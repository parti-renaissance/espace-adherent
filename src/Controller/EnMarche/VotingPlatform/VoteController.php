<?php

namespace App\Controller\EnMarche\VotingPlatform;

use App\Entity\VotingPlatform\Election;
use App\Form\VotingPlatform\CommitteeAdherentCandidatesType;
use App\Form\VotingPlatform\VoteCandidateType;
use App\VotingPlatform\Designation\DesignationTypeEnum;
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
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canVote($voteCommand)) {
            return $this->redirectToElectionRoute('app_voting_platform_index', $election);
        }

        $this->processor->doVote($voteCommand);

        $form = $this
            ->createForm(
                $this->getCandidateFormType($election),
                $voteCommand,
                ['candidates' => $candidateGroups = $this->candidateGroupRepository->findForElection($election)]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doConfirm($voteCommand);

            return $this->redirectToElectionRoute('app_voting_platform_confirm_step', $election);
        }

        return $this->renderElectionTemplate(
            sprintf('voting_platform/vote_step/%s.html.twig', $election->getDesignationType()),
            $election,
            [
                'form' => $form->createView(),
                'candidate_groups' => $candidateGroups,
            ]
        );
    }

    private function getCandidateFormType(Election $election): string
    {
        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            return CommitteeAdherentCandidatesType::class;
        }

        return VoteCandidateType::class;
    }
}
