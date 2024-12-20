<?php

namespace App\Controller\Renaissance\Election;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\LocalElection\LocalElectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/elections-departementales/{uuid}')]
class DepartmentElectionController extends AbstractController
{
    public function __construct(private readonly LocalElectionRepository $localElectionRepository)
    {
    }

    #[Route(path: '', name: 'app_renaissance_departmental_election_lists', methods: 'GET')]
    public function candidaturesListAction(Designation $designation): Response
    {
        if (!$localElection = $this->localElectionRepository->findByDesignation($designation)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        return $this->render('renaissance/local_election/lists.html.twig', [
            'designation' => $designation,
            'candidacies_groups' => $localElection->getCandidaciesGroups(),
        ]);
    }

    #[Route(path: '/reglement-interieur', name: 'app_renaissance_departmental_election_rules', methods: 'GET')]
    public function rulesAction(Designation $designation): Response
    {
        return $this->render('renaissance/local_election/rules.html.twig', [
            'designation' => $designation,
        ]);
    }
}
