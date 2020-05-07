<?php

namespace AppBundle\Controller\EnMarche\VotingPlatform;

use AppBundle\Entity\VotingPlatform\Election;
use AppBundle\Form\ConfirmActionType;
use AppBundle\Form\VotingPlatform\CommitteeAdherentCandidatesType;
use AppBundle\Form\VotingPlatform\VoteCandidateType;
use AppBundle\Repository\VotingPlatform\CandidateGroupRepository;
use AppBundle\VotingPlatform\Designation\DesignationTypeEnum;
use AppBundle\VotingPlatform\Election\RedirectManager;
use AppBundle\VotingPlatform\Election\VoteCommandProcessor;
use AppBundle\VotingPlatform\Election\VoteCommandStorage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections/{uuid}", name="app_voting_platform")
 *
 * @Security("is_granted('ROLE_ADHERENT')")
 */
class VotingPlatformController extends AbstractController
{
    private $redirectManager;
    private $storage;
    private $processor;
    private $candidateGroupRepository;

    public function __construct(
        RedirectManager $redirectManager,
        VoteCommandStorage $storage,
        VoteCommandProcessor $processor,
        CandidateGroupRepository $candidateGroupRepository
    ) {
        $this->redirectManager = $redirectManager;
        $this->storage = $storage;
        $this->processor = $processor;
        $this->candidateGroupRepository = $candidateGroupRepository;
    }

    /**
     * @Route("", name="_index", methods={"GET"})
     */
    public function indexAction(Election $election): Response
    {
        $voteCommand = $this->storage->getVoteCommand($election);

        if (!$this->processor->canStart($voteCommand)) {
            return $this->redirect($this->redirectManager->getRedirection($election));
        }

        $this->processor->doStart($voteCommand);

        return $this->renderElectionTemplate('voting_platform/index.html.twig', $election);
    }

    /**
     * @Route("/vote", name="_vote_step", methods={"GET", "POST"})
     */
    public function voteAction(Request $request, Election $election): Response
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

    /**
     * @Route("/confirmer", name="_confirm_step", methods={"GET", "POST"})
     */
    public function confirmAction(Request $request, Election $election): Response
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

        if ($form->isSubmitted() && $form->isValid() && $form->get('allow')->isClicked()) {
            $this->processor->doFinish($voteCommand);

            return $this->redirectToElectionRoute('app_voting_platform_finish_step', $election);
        }

        return $this->renderElectionTemplate('voting_platform/confirmation.html.twig', $election, [
            'form' => $form->createView(),
            'vote_command' => $voteCommand,
            'candidate_groups' => $this->candidateGroupRepository->findByUuids($voteCommand->getCandidateGroupUuids()),
        ]);
    }

    /**
     * @Route("/fin", name="_finish_step", methods={"GET"})
     */
    public function finishAction(Election $election): Response
    {
        return $this->renderElectionTemplate('voting_platform/finish.html.twig', $election);
    }

    private function getCandidateFormType(Election $election): string
    {
        if (DesignationTypeEnum::COMMITTEE_ADHERENT === $election->getDesignationType()) {
            return CommitteeAdherentCandidatesType::class;
        }

        return VoteCandidateType::class;
    }

    protected function redirectToElectionRoute(string $routeName, Election $election): Response
    {
        return $this->redirectToRoute($routeName, ['uuid' => $election->getUuid()]);
    }

    private function renderElectionTemplate(string $template, Election $election, array $params = []): Response
    {
        return $this->render($template, array_merge($params, [
            'base_layout' => sprintf('voting_platform/_layout_%s.html.twig', $election->getDesignationType()),
            'election' => $election,
        ]));
    }
}
