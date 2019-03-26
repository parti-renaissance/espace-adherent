<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\ReferentArea;
use AppBundle\Entity\Referent;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\ReferentRepository;
use AppBundle\Repository\ReferentTagRepository;
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
     * @Route("", name="our_referents_homepage")
     * @Method("GET")
     */
    public function indexAction(AdherentRepository $adherentRepository, ReferentTagRepository $referentTagRepository): Response
    {
        $doctrine = $this->getDoctrine();
        $referentAreasRepository = $doctrine->getRepository(ReferentArea::class);

        return $this->render('referent/nomination/homepage.html.twig', [
            'referents' => $adherentRepository->findReferentsByStatusOrderedByAreaLabel(),
            'groupedZones' => $referentAreasRepository->findAllGrouped(),
        ]);
    }

    /**
     * @Route("/{slug}", name="our_referents_referent")
     * @Method("GET")
     */
    public function candidateAction(Referent $referent): Response
    {
        return $this->render('referent/nomination/referent.html.twig', [
            'referent' => $referent,
        ]);
    }
}
