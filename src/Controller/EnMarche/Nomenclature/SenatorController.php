<?php

namespace AppBundle\Controller\EnMarche\Nomenclature;

use AppBundle\Entity\Nomenclature\Senator;
use AppBundle\Repository\Nomenclature\SenatorAreaRepository;
use AppBundle\Repository\Nomenclature\SenatorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/le-mouvement/senateurs", name="app_nomenclature_senator_")
 */
class SenatorController extends Controller
{
    /**
     * @Route(name="list", methods={"GET"})
     */
    public function list(SenatorRepository $senatorRepository, SenatorAreaRepository $senatorAreaRepository): Response
    {
        return $this->render('nomenclature/senator/list.html.twig', [
            'senators' => $senatorRepository->findByStatus(),
            'areas' => $senatorAreaRepository->findAllOrdered(),
        ]);
    }

    /**
     * @Route("/{slug}", name="show", methods={"GET"})
     */
    public function show(Senator $senator): Response
    {
        return $this->render('nomenclature/senator/show.html.twig', [
            'senator' => $senator,
        ]);
    }
}
