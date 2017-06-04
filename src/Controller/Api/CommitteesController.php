<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class CommitteesController extends Controller
{
    /**
     * @Route("/committees", defaults={"_enable_campaign_silence"=true}, name="api_committees")
     * @Method("GET")
     */
    public function getApprovedCommitteesAction()
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }
}
