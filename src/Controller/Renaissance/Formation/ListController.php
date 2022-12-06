<?php

namespace App\Controller\Renaissance\Formation;

use App\Repository\AdherentFormation\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/formations", name="app_renaissance_adherent_formation_list", methods={"GET"})
 */
class ListController extends AbstractController
{
    public function __construct(private readonly FormationRepository $formationRepository)
    {
    }

    public function __invoke(): Response
    {
        return $this->render('renaissance/formation/list.html.twig', [
            'formations' => $this->formationRepository->findAll(),
        ]);
    }
}
