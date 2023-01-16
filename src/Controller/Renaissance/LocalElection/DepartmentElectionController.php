<?php

namespace App\Controller\Renaissance\LocalElection;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\LocalElection\LocalElectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections-departementales/{uuid}")
 * @IsGranted("ROLE_ADHERENT")
 */
class DepartmentElectionController extends AbstractController
{
    public function __construct(private readonly LocalElectionRepository $localElectionRepository)
    {
    }

    /**
     * @Route("", name="app_renaissance_departmental_election_lists", methods="GET")
     */
    public function candidaturesListAction(Designation $designation): Response
    {
        if (!$localElection = $this->localElectionRepository->findByDesignation($designation)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        return $this->render('renaissance/local_election/lists.html.twig', [
            'designation' => $designation,
            'candidacies_groups' => $localElection->getCandidaciesGroups(),
        ]);
    }

    /**
     * @Route("/reglement-interieur", name="app_renaissance_departmental_election_rules", methods="GET")
     */
    public function rulesAction(Designation $designation): Response
    {
        return $this->render('renaissance/local_election/rules.html.twig', [
            'designation' => $designation,
        ]);
    }
}
