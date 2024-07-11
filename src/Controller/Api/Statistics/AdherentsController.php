<?php

namespace App\Controller\Api\Statistics;

use App\History\CommitteeMembershipHistoryHandler;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\AdherentManager;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_OAUTH_SCOPE_READ:STATS')]
#[Route(path: '/statistics/adherents')]
class AdherentsController extends AbstractStatisticsController
{
    #[Route(path: '/count', name: 'app_statistics_adherents_count', methods: ['GET'])]
    public function adherentsCountAction(AdherentRepository $adherentRepository): Response
    {
        $count = $adherentRepository->countByGender();

        return new JsonResponse($this->aggregateCount($count));
    }

    #[Route(path: '/count-by-referent-area', name: 'app_statistics_adherents_count_for_referent_managed_area', methods: ['GET'])]
    public function adherentsCountForReferentManagedAreaAction(
        Request $request,
        EmailSubscriptionHistoryHandler $historyHandler,
        AdherentManager $adherentManager,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler
    ): Response {
        $referent = $this->findReferent($request);
        $count = $this->adherentRepository->countByGenderManagedBy($referent);

        return new JsonResponse(
            array_merge(
                $this->aggregateCount($count),
                ['adherents' => $adherentManager->countMembersByMonthManagedBy($referent)],
                ['committee_members' => $committeeMembershipHistoryHandler->queryCountByMonth($referent)],
                ['email_subscriptions' => $historyHandler->queryCountByMonth($referent)]
            )
        );
    }

    private function aggregateCount(array $count): array
    {
        array_walk($count, function (&$item) {
            $item = (int) $item['count'];
        });

        $count['total'] = array_sum($count);

        return $count;
    }
}
