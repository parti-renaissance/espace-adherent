<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Entity\LegislativeCandidate;
use AppBundle\Entity\LegislativeDistrictZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="legislatives_homepage")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        return $this->render('legislatives/homepage.html.twig', [
            'candidates' => $this->getDoctrine()->getRepository(LegislativeCandidate::class)->findAllForDirectory(),
            'groupedZones' => $this->getDoctrine()->getRepository(LegislativeDistrictZone::class)->findAllGrouped(),
        ]);
    }

    /**
     * @Route("/redirection-en-marche", defaults={"_enable_campaign_silence"=true}, name="legislatives_redirect_en_marche")
     * @Method("GET")
     */
    public function redirectEnMarcheAction(): Response
    {
        return $this->redirect('https://en-marche.fr', Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/candidat/{slug}", defaults={"_enable_campaign_silence"=true}, name="legislatives_candidate")
     * @Method("GET")
     */
    public function candidateAction(LegislativeCandidate $candidate): Response
    {
        return $this->render('legislatives/candidate.html.twig', [
            'candidate' => $candidate,
        ]);
    }
}
