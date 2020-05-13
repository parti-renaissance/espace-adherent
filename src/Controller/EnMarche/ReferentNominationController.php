<?php

namespace App\Controller\EnMarche;

use App\Entity\Referent;
use App\Entity\ReferentArea;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/le-mouvement/nos-referents")
 */
class ReferentNominationController extends Controller
{
    /**
     * @Route("", name="our_referents_homepage", methods={"GET"})
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
     * @Route("/{slug}", name="our_referents_referent", methods={"GET"})
     */
    public function candidateAction(Referent $referent): Response
    {
        return $this->render('referent/nomination/referent.html.twig', [
            'referent' => $referent,
        ]);
    }
}
