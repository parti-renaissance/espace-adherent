<?php

namespace App\Controller\Renaissance\Election\Summary;

use App\LocalElection\LocalElectionCollection;
use App\Repository\LocalElection\LocalElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/elections-assemblees-departementales', name: 'app_renaissance_local_election_summary_list', methods: ['GET'])]
class ListController extends AbstractController
{
    public function __invoke(LocalElectionRepository $localElectionRepository)
    {
        return $this->render('renaissance/local_election/summary/list.html.twig', [
            'elections' => new LocalElectionCollection($localElectionRepository->findUpcomingDepartmentElections()),
        ]);
    }
}
