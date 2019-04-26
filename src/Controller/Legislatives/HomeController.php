<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Entity\LegislativeCandidate;
use AppBundle\Entity\LegislativeDistrictZone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    /**
     * @Route("/", name="legislatives_homepage", methods={"GET"})
     */
    public function indexAction(Request $request): Response
    {
        $doctrine = $this->getDoctrine();
        $candidatesRepository = $doctrine->getRepository(LegislativeCandidate::class);
        $districtZonesRepository = $doctrine->getRepository(LegislativeDistrictZone::class);

        $status = $request->query->get('status');
        if (!\in_array($status, LegislativeCandidate::getStatuses(), true)) {
            $status = null;
        }

        return $this->render('legislatives/homepage.html.twig', [
            'status' => $status,
            'candidates' => $candidatesRepository->findAllForDirectory($status),
            'groupedZones' => $districtZonesRepository->findAllGrouped(),
        ]);
    }

    /**
     * @Route("/redirection-en-marche", name="legislatives_redirect_en_marche", methods={"GET"})
     */
    public function redirectEnMarcheAction(): Response
    {
        return $this->redirect('https://en-marche.fr', Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/candidat/{slug}", name="legislatives_candidate", methods={"GET"})
     */
    public function candidateAction(LegislativeCandidate $candidate): Response
    {
        return $this->render('legislatives/candidate.html.twig', [
            'candidate' => $candidate,
        ]);
    }
}
