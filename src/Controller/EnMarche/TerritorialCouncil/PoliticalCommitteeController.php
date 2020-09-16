<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\CanaryControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comite-politique", name="app_political_committee_")
 *
 * @Security("is_granted('POLITICAL_COMMITTEE_MEMBER')")
 */
class PoliticalCommitteeController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        return $this->render('territorial_council/political_committee/index.html.twig');
    }
}
