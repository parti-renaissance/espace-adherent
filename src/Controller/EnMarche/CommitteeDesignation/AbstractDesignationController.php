<?php

namespace App\Controller\EnMarche\CommitteeDesignation;

use App\Entity\Committee;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\ElectionRound;
use App\Repository\VotingPlatform\CandidateGroupRepository;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use App\VotingPlatform\VoteResult\VoteResultAggregator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        return $this->renderTemplate('committee_designation/list.html.twig', $request, [
            'committee' => $committee,
            'elections' => $this->electionRepository->getAllAggregatedDataForCommittee($committee),
        ]);
    }

    /**
     * @Route("/{uuid}/{election_round_uuid}", name="_dashboard", methods={"GET"}, defaults={"election_round_uuid": null})
     *
     * @ParamConverter("electionRound", options={"mapping": {"election_round_uuid": "uuid"}})
     */
    public function dashboardAction(
        Request $request,
        Committee $committee,
        Election $election,
        ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound()
            ;
        }

        return $this->renderTemplate('committee_designation/dashboard.html.twig', $request, [
            'committee' => $committee,
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
        ]);
    }

    /**
     * @Route(
     *     "/{uuid}/liste-emargement/{election_round_uuid}",
     *     name="_voters_list",
     *     methods={"GET"},
     *     defaults={"election_round_uuid": null}
     * )
     *
     * @ParamConverter("electionRound", options={"mapping": {"election_round_uuid": "uuid"}})
     */
    public function listVotersAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoterRepository $voterRepository,
        ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound()
            ;
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        return $this->renderTemplate('committee_designation/voters_list.html.twig', $request, [
            'committee' => $committee,
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'voters' => $voterRepository->findForElectionRound($electionRound),
        ]);
    }

    /**
     * @Route(
     *     "/{uuid}/resultats/{election_round_uuid}",
     *     name="_results",
     *     methods={"GET"},
     *     defaults={"election_round_uuid": null}
     * )
     *
     * @ParamConverter("electionRound", options={"mapping": {"election_round_uuid": "uuid"}})
     */
    public function showResultsAction(
        Request $request,
        Committee $committee,
        Election $election,
        CandidateGroupRepository $candidateGroupRepository,
        VoteResultAggregator $aggregator,
        ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound()
            ;
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        $candidateGroups = $candidateGroupRepository->findForElectionRound($electionRound);

        return $this->renderTemplate('committee_designation/results.html.twig', $request, [
            'committee' => $committee,
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'candidate_groups' => $request->query->has('femme') ?
                $candidateGroups->getWomanCandidateGroups() :
                $candidateGroups->getManCandidateGroups(),
            'results' => $election->isResultPeriodActive() ? $aggregator->getResultsForRound($electionRound)['aggregated']['candidates'] : [],
        ]);
    }

    /**
     * @Route(
     *     "/{uuid}/bulletins/{election_round_uuid}",
     *     name="_votes",
     *     methods={"GET"},
     *     defaults={"election_round_uuid": null}
     * )
     *
     * @ParamConverter("electionRound", options={"mapping": {"election_round_uuid": "uuid"}})
     */
    public function listVotesAction(
        Request $request,
        Committee $committee,
        Election $election,
        VoteResultAggregator $aggregator,
        ElectionRound $electionRound = null
    ): Response {
        if (!$electionRound) {
            $electionRound = $election->isSecondRoundVotePeriodActive()
                ? $election->getFirstRound()
                : $election->getCurrentRound()
            ;
        }

        if (!$this->hasAccess($election, $electionRound)) {
            return $this->redirectToSpaceRoute('dashboard', $committee, $election);
        }

        return $this->renderTemplate('committee_designation/votes_list.html.twig', $request, [
            'committee' => $committee,
            'election_round' => $electionRound,
            'election_stats' => $this->electionRepository->getSingleAggregatedData($electionRound),
            'votes' => $aggregator->getResultsForRound($electionRound)['vote_results'],
        ]);
    }

    abstract protected function getSpaceType(): string;

    protected function renderTemplate(string $template, Request $request, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('committee_designation/_base_%s.html.twig', $messageType = $this->getSpaceType()),
                'space_type' => $messageType,
                'route_params' => $this->getRouteParameters($request),
            ]
        ));
    }

    protected function redirectToSpaceRoute(
        string $subName,
        Committee $committee,
        Election $election,
        array $parameters = []
    ): Response {
        return $this->redirectToRoute("app_{$this->getSpaceType()}_designations_${subName}", array_merge([
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
