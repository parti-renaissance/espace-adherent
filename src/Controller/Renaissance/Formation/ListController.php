<?php

namespace App\Controller\Renaissance\Formation;

use App\Entity\Adherent;
use App\Repository\AdherentFormation\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/espace-adherent/formations", name="app_renaissance_adherent_formation_list", methods={"GET"})
 */
class ListController extends AbstractController
{
    public function __construct(private readonly FormationRepository $formationRepository)
    {
    }

    public function __invoke(): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$adherent->isRenaissanceUser()) {
            return $this->redirect($this->generateUrl('app_renaissance_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        return $this->render('renaissance/formation/list.html.twig', [
            'formations' => $this->formationRepository->findAllVisible(),
        ]);
    }
}
