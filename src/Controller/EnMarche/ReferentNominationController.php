<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\ReferentArea;
use AppBundle\Entity\Referent;
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
     * @Route("", defaults={"_enable_campaign_silence"=true}, name="our_referents_homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        $doctrine = $this->getDoctrine();
        $referentsRepository = $doctrine->getRepository(Referent::class);
        $referentAreasRepository = $doctrine->getRepository(ReferentArea::class);

        return $this->render('referent/nomination/homepage.html.twig', [
            'referents' => $referentsRepository->findByStatusOrderedByAreaLabel(),
            'groupedZones' => $referentAreasRepository->findAllGrouped(),
        ]);
    }

    /**
     * @Route("/{slug}", defaults={"_enable_campaign_silence"=true}, name="our_referents_referent")
     * @Method("GET")
     */
    public function candidateAction(Referent $referent): Response
    {
        return $this->render('referent/nomination/referent.html.twig', [
            'referent' => $referent,
        ]);
    }
}
