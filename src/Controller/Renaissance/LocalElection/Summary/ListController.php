<?php

namespace App\Controller\Renaissance\LocalElection\Summary;

use App\Controller\CanaryControllerTrait;
use App\LocalElection\LocalElectionCollection;
use App\Repository\LocalElection\LocalElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections-locales", name="app_renaissance_local_election_summary_list", methods={"GET"})
 */
class ListController extends AbstractController
{
    use CanaryControllerTrait;

    public function __invoke(LocalElectionRepository $localElectionRepository)
    {
        $this->disableInProduction();

        return $this->render('renaissance/local_election/summary/list.html.twig', [
            'elections' => new LocalElectionCollection($localElectionRepository->findUpcomingDepartmentElections()),
        ]);
    }
}
