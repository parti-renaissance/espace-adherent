<?php

namespace App\Controller\EnMarche\TerritorialCouncil\Designation;

use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Repository\VotingPlatform\VoterRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-referent/instances/designations/{uuid}/resultats', name: 'app_territorial_council_referent_designation_results', requirements: ['uuid' => '%pattern_uuid%'])]
#[Security("is_granted('ROLE_REFERENT') and is_granted('CAN_MANAGE_TERRITORIAL_COUNCIL', election.getElectionEntity().getTerritorialCouncil())")]
class ElectionResultsController extends AbstractController
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    #[Route(path: '/{election_round_uuid}', name: '_dashboard', methods: ['GET'], defaults: ['election_round_uuid' => null], requirements: ['election_round_uuid' => '%pattern_uuid%'])]
    #[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
    public function dashboardAction(Election $election, ?ElectionRound $electionRound = null): Response
    {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        return $this->render('territorial_council_designation/election_results/dashboard.html.twig', [
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
        ]);
    }

    #[Route(path: '/liste-emargement/{election_round_uuid}', name: '_voters_list', methods: ['GET'], defaults: ['election_round_uuid' => null], requirements: ['election_round_uuid' => '%pattern_uuid%'])]
    #[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
    public function listVotersAction(
        Election $election,
        VoterRepository $voterRepository,
        ?ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToRoute('app_territorial_council_referent_designation_results_dashboard', ['uuid' => $election->getUuid()]);
        }

        return $this->render('territorial_council_designation/election_results/voters_list.html.twig', [
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'voters' => $voterRepository->findForElectionRound($electionRound),
        ]);
    }

    #[Route(path: '/voir-par-groupe/{election_round_uuid}', name: '_by_pool', methods: ['GET'], defaults: ['election_round_uuid' => null], requirements: ['election_round_uuid' => '%pattern_uuid%'])]
    #[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
    public function showResultsAction(
        Request $request,
        Election $election,
        ?ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToRoute('app_territorial_council_referent_designation_results_dashboard', ['uuid' => $election->getUuid()]);
        }

        return $this->render('territorial_council_designation/election_results/results.html.twig', [
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'election_pool_result' => current(array_filter(
                $election->getElectionResult()->getElectionRoundResult($electionRound)->getElectionPoolResults(),
                function (ElectionPoolResult $poolResult) use ($request) {
                    return $poolResult->getElectionPool()->getCode() === $request->query->get('pool_code');
                }
            )),
        ]);
    }

    #[Route(path: '/bulletins/{election_round_uuid}', name: '_votes', methods: ['GET'], defaults: ['election_round_uuid' => null], requirements: ['election_round_uuid' => '%pattern_uuid%'])]
    #[ParamConverter('electionRound', options: ['mapping' => ['election_round_uuid' => 'uuid']])]
    public function listVotesAction(
        Election $election,
        VoteResultRepository $voteResultRepository,
        ?ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToRoute('app_territorial_council_referent_designation_results_dashboard', ['uuid' => $election->getUuid()]);
        }

        return $this->render('territorial_council_designation/election_results/votes_list.html.twig', [
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'votes' => $voteResultRepository->getResultsForRound($electionRound),
        ]);
    }

    private function hasAccess(Election $election, ElectionRound $electionRound): bool
    {
        if (!$electionRound->isRoundOf($election)) {
            return false;
        }

        if ($election->isVotePeriodActive() && !$election->isSecondRoundVotePeriodActive()) {
            return false;
        }

        if ($election->isSecondRoundVotePeriodActive() && $electionRound->isActive()) {
            return false;
        }

        return true;
    }
}
