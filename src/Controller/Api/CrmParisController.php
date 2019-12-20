<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crm-paris")
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_CRM_PARIS')")
 */
class CrmParisController extends Controller
{
    /**
     * @Route("/adherents", name="app_crm_paris_adherents", methods={"GET"})
     */
    public function adherentsAction(): Response
    {
        return new Response('OK');
    }
}
