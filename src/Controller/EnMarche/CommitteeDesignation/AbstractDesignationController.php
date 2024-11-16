<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionResult\ElectionPoolResult;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\CommitteeElectionRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoteResultRepository;
use App\Repository\VotingPlatform\VoterRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

abstract class AbstractDesignationController extends AbstractController
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    #[Route(path: '', name: '_list', methods: ['GET'])]
    public function listDesignationsAction(
        Request $request,
        Committee $committee,
        CommitteeElectionRepository $repository,
    ): Response {
        return $this->renderTemplate('committee_designation/list.html.twig', $request, [
            'committee' => $committee,
            'elections' => $repository->findElections(new CommitteeDesignationsListFilter([], $committee), 1, 200),
        ]);
    }

    /**
     * @param Committee $committee used in Security notation in concretes classes
     */
    #[Route(path: '/{uuid}/{election_round_uuid}', name: '_dashboard', methods: ['GET'], defaults: ['election_round_uuid' => null])]
    public function dashboardAction(
        Request $request,
        Committee $committee,
        Election $election,
        #[MapEntity(mapping: ['election_round_uuid' => 'uuid'])]
        ?ElectionRound $electionRound = null,
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        return $this->renderTemplate('committee_designation/dashboard.html.twig', $request, [
            'election_round' => $electionRound,
            'committee' => $committee,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
        ]);
    }

    #[Route(path: '/{uuid}/liste-emargement/{election_round_uuid}', name: '_voters_list', methods: ['GET'], defaults: ['election_round_uuid' => null])]
    public function listVotersAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoterRepository $voterRepository,
        #[MapEntity(mapping: ['election_round_uuid' => 'uuid'])]
        ?ElectionRound $electionRound = null,
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        return $this->renderTemplate('committee_designation/voters_list.html.twig', $request, [
            'election_round' => $electionRound,
            'committee' => $committee,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'voters' => $voterRepository->findForElectionRound($electionRound),
        ]);
    }

    #[Route(path: '/{uuid}/resultats/{election_round_uuid}', name: '_results', methods: ['GET'], defaults: ['election_round_uuid' => null])]
    public function showResultsAction(
        Request $request,
        Committee $committee,
        Election $election,
        #[MapEntity(mapping: ['election_round_uuid' => 'uuid'])]
        ?ElectionRound $electionRound = null,
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        $poolCode = $request->query->get('code');

        return $this->renderTemplate('committee_designation/results.html.twig', $request, [
            'election_round' => $electionRound,
            'committee' => $committee,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'election_pool_result' => current(array_filter(
                $election->getElectionResult()->getElectionRoundResult($electionRound)->getElectionPoolResults(),
                function (ElectionPoolResult $poolResult) use ($poolCode) {
                    return $poolResult->getElectionPool()->getCode() === $poolCode;
                }
            )),
        ]);
    }

    #[Route(path: '/{uuid}/bulletins/{election_round_uuid}', name: '_votes', methods: ['GET'], defaults: ['election_round_uuid' => null])]
    public function listVotesAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoteResultRepository $voteResultRepository,
        #[MapEntity(mapping: ['election_round_uuid' => 'uuid'])]
        ?ElectionRound $electionRound = null,
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound();
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        return $this->renderTemplate('committee_designation/votes_list.html.twig', $request, [
            'election_round' => $electionRound,
            'committee' => $committee,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'votes' => $voteResultRepository->getResultsForRound($electionRound),
        ]);
    }

    abstract protected function getSpaceType(): string;

    protected function renderTemplate(string $template, Request $request, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('committee_designation/_base_%s.html.twig', $spaceType = $this->getSpaceType()),
                'space_type' => $spaceType,
                'route_params' => $this->getRouteParameters($request),
            ]
        ));
    }

    protected function redirectToSpaceRoute(
        string $subName,
        Committee $committee,
        Election $election,
        array $parameters = [],
    ): Response {
        return $this->redirectToRoute("app_{$this->getSpaceType()}_designations_{$subName}", array_merge([
            'committee_slug' => $committee->getSlug(),
            'uuid' => $election->getUuid()->toString(),
        ], $parameters));
    }

    protected function getRouteParameters(Request $request): array
    {
        return [
            'committee_slug' => $request->attributes->get('committee_slug'),
        ];
    }

    protected function hasAccess(Election $election, ElectionRound $electionRound): bool
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
