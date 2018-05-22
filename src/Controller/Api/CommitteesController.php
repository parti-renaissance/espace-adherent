<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CommitteesController extends Controller
{
    /**
     * @Route("/committees", defaults={"_enable_campaign_silence"=true}, name="api_committees")
     * @Method("GET")
     */
    public function getApprovedCommitteesAction(): Response
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }

    /**
     * @Route("/committees/count-for-referent-area", name="app_committees_count_for_referent_area")
     * @Method("GET")
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function getCommitteeCountersAction(AdherentRepository $adherentRepository, CommitteeRepository $committeeRepository): Response
    {
        $referent = $this->getUser();

        return new JsonResponse([
            'committees' => $committeeRepository->countApprovedForReferent($referent),
            'supervisors' => $adherentRepository->countSupervisorsByGenderForReferent($referent),
        ]);
    }
}
