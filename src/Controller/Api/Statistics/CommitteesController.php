<?php

namespace App\Controller\Api\Statistics;

use App\History\CommitteeMembershipHistoryHandler;
use App\Repository\CommitteeRepository;
use App\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
#[Route(path: '/statistics/committees')]
class CommitteesController extends AbstractStatisticsController
{
    #[Route(path: '/count-for-referent-area', name: 'app_statistics_committees_count_for_referent_area', methods: ['GET'])]
    public function getCommitteeCountersAction(Request $request, CommitteeRepository $committeeRepository): Response
    {
        $referent = $this->findReferent($request);

        return new JsonResponse([
            'committees' => $committeeRepository->countApprovedForReferent($referent),
            'members' => $this->adherentRepository->countMembersByGenderForReferent($referent),
            'supervisors' => $this->adherentRepository->countSupervisorsByGenderForReferent($referent),
        ]);
    }

    #[Route(path: '/members/count-by-month', name: 'app_statistics_committee_members_count_by_month_for_referent_area', methods: ['GET'])]
    public function getMembersCommitteeCountAction(
        Request $request,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler,
        CommitteeRepository $repository
    ): Response {
        $referent = $this->findReferent($request);
        $filter = StatisticsParametersFilter::createFromRequest($request, $repository);

        return new JsonResponse(['committee_members' => $committeeMembershipHistoryHandler->queryCountByMonth($referent, 6, $filter)]);
    }

    #[Route(path: '/top-5-in-referent-area', name: 'app_statistics_most_active_committees', methods: ['GET'])]
    public function getTopCommitteesInReferentManagedAreaAction(
        Request $request,
        CommitteeRepository $committeeRepository
    ): Response {
        $referent = $this->findReferent($request);

        return new JsonResponse([
            'most_active' => $committeeRepository->retrieveMostActiveCommitteesInReferentManagedArea($referent),
            'least_active' => $committeeRepository->retrieveLeastActiveCommitteesInReferentManagedArea($referent),
        ]);
    }
}
