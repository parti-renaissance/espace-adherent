<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\History\CommitteeMembershipHistoryHandler;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\AdherentManager;
use App\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/statistics/adherents")
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
 */
class AdherentsController extends Controller
{
    /**
     * @Route("/count", name="app_adherents_count", methods={"GET"})
     */
    public function adherentsCountAction(AdherentRepository $adherentRepository): Response
    {
        $count = $adherentRepository->countByGender();

        return new JsonResponse($this->aggregateCount($count));
    }

    /**
     * @Route("/count-by-referent-area", name="app_adherents_count_for_referent_managed_area", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     */
    public function adherentsCountForReferentManagedAreaAction(
        Adherent $referent,
        AdherentRepository $adherentRepository,
        EmailSubscriptionHistoryHandler $historyHandler,
        AdherentManager $adherentManager,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler
    ): Response {
        $count = $adherentRepository->countByGenderManagedBy($referent);

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
