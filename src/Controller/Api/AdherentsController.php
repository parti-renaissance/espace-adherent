<?php

namespace AppBundle\Controller\Api;

use AppBundle\History\CommitteeMembershipHistoryHandler;
use AppBundle\Membership\AdherentManager;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Repository\AdherentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/adherents")
 * @Security("is_granted('ROLE_REFERENT')")
 */
class AdherentsController extends Controller
{
    /**
     * @Route("/count", name="app_adherents_count")
     * @Method("GET")
     */
    public function adherentsCountAction(AdherentRepository $adherentRepository): Response
    {
        $count = $adherentRepository->countByGender();

        return new JsonResponse($this->aggregateCount($count));
    }

    /**
     * @Route("/count-by-referent-area", name="app_adherents_count_for_referent_managed_area")
     * @Method("GET")
     */
    public function adherentsCountForReferentManagedAreaAction(
        AdherentRepository $adherentRepository,
        EmailSubscriptionHistoryHandler $historyHandler,
        AdherentManager $adherentManager,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler
    ): Response {
        $referent = $this->getUser();
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
