<?php

namespace App\Controller\EnMarche\Designation;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\VoteResult\VoteResultAggregator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractDesignationController extends AbstractController
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    /**
     * @Route("", name="_list", methods={"GET"})
     */
    public function listDesignationsAction(Request $request, Committee $committee): Response
    {
        return $this->renderTemplate('designation/list.html.twig', $request, [
            'committee' => $committee,
            'elections' => $this->electionRepository->getAllAggregatedDataForCommittee($committee),
        ]);
    }

    /**
     * @Route("/{uuid}/liste-emargement", name="_voters_list", methods={"GET"})
     */
    public function listVotersAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoterRepository $voterRepository
    ): Response {
        return $this->renderTemplate('designation/voters_list.html.twig', $request, [
            'committee' => $committee,
            'election' => $election,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($election),
            'voters' => $voterRepository->findForElection($election),
        ]);
    }

    /**
     * @Route("/{uuid}/resultats", name="_results", methods={"GET"})
     */
    public function showResultsAction(
        Request $request,
        Committee $committee,
        Election $election,
        CandidateGroupRepository $candidateGroupRepository,
        VoteResultAggregator $aggregator
    ): Response {
        $candidateGroups = $candidateGroupRepository->findForElectionRound($election->getCurrentRound());

        return $this->renderTemplate('designation/results.html.twig', $request, [
            'committee' => $committee,
            'election' => $election,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($election),
            'candidate_groups' => $request->query->has('femme') ?
                $candidateGroups->getWomanCandidateGroups() :
                $candidateGroups->getManCandidateGroups(),
            'results' => $election->isResultPeriodActive() ? $aggregator->getResults($election)['aggregated']['candidates'] : [],
        ]);
    }

    /**
     * @Route("/{uuid}/bulletins", name="_votes", methods={"GET"})
     */
    public function listVotesAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoteResultAggregator $aggregator
    ): Response {
        return $this->renderTemplate('designation/votes_list.html.twig', $request, [
            'committee' => $committee,
            'election' => $election,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($election),
            'votes' => $aggregator->getResults($election)['vote_results'],
        ]);
    }

    abstract protected function getSpaceType(): string;

    protected function renderTemplate(string $template, Request $request, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('designation/_base_%s.html.twig', $messageType = $this->getSpaceType()),
                'space_type' => $messageType,
                'route_params' => $this->getRouteParameters($request),
            ]
        ));
    }

    protected function getRouteParameters(Request $request): array
    {
        return [
            'committee_slug' => $request->attributes->get('committee_slug'),
        ];
    }
}
