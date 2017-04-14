<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-candidat-legislatives")
 * @Security("is_granted('ROLE_LEGISLATIVE_CANDIDATE')")
 */
class LegislativesController extends Controller
{
    /**
     * @Route("", name="app_legislative_candidates_platform")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        return $this->render('legislatives/platform.html.twig');
    }
}
