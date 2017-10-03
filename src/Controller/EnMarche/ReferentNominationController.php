<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\LegislativeCandidate;
use AppBundle\Entity\LegislativeDistrictZone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/le-mouvement/nos-referents")
 */
class ReferentNominationController extends Controller
{
    /**
     * @Route("/", defaults={"_enable_campaign_silence"=true}, name="nominations_referents_homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        $doctrine = $this->getDoctrine();
        $adherentsRepository = $doctrine->getRepository(Adherent::class);
        $districtZonesRepository = $doctrine->getRepository(LegislativeDistrictZone::class);

        return $this->render('legislatives/homepage.html.twig', [
            'candidates' => $adherentsRepository->findReferents(),
            'groupedZones' => $districtZonesRepository->findAllGrouped(),
        ]);
    }


    /**
     * @Route("/referent/{slug}", defaults={"_enable_campaign_silence"=true}, name="nominations_referents_referent")
     * @Method("GET")
     */
    public function candidateAction(LegislativeCandidate $candidate): Response
    {
        return $this->render('legislatives/candidate.html.twig', [
            'candidate' => $candidate,
        ]);
    }
}
