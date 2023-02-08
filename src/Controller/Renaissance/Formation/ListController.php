<?php

namespace App\Controller\Renaissance\Formation;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Repository\AdherentFormation\FormationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/formations", name="app_renaissance_adherent_formation_list", methods={"GET"})
 * @IsGranted("RENAISSANCE_ADHERENT")
 */
class ListController extends AbstractController
{
    use CanaryControllerTrait;

    public function __construct(private readonly FormationRepository $formationRepository)
    {
    }

    public function __invoke(): Response
    {
        $this->disableInProduction();

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render('renaissance/formation/list.html.twig', [
            'national_formations' => $this->formationRepository->findAllNational(),
            'local_formations' => $this->formationRepository->findAllLocal($adherent->getZones()->toArray()),
        ]);
    }
}
