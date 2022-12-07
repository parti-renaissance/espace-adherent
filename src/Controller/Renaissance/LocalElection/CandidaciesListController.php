<?php

namespace App\Controller\Renaissance\LocalElection;

use App\Entity\Adherent;
use App\LocalElection\Manager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections-departementales", name="app_renaissance_departmental_election_lists", methods="GET")
 * @IsGranted("ROLE_ADHERENT")
 */
class CandidaciesListController extends AbstractController
{
    public function __invoke(Manager $manager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$localElection = $manager->getLastLocalElection($adherent)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        return $this->render('renaissance/local_election/lists.html.twig', [
            'designation' => $localElection->getDesignation(),
            'candidacies_groups' => $localElection->getCandidaciesGroups(),
        ]);
    }
}
