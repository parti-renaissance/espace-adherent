<?php

namespace App\Controller\Renaissance\Election\Summary;

use App\Entity\LocalElection\LocalElection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/elections-assemblees-departementales/{uuid}', name: 'app_renaissance_local_election_summary_candidacies', methods: ['GET'])]
class CandidaciesController extends AbstractController
{
    public function __invoke(LocalElection $localElection)
    {
        return $this->render('renaissance/local_election/summary/candidacies.html.twig', [
            'election' => $localElection,
        ]);
    }
}
