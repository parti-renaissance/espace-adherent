<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\CommitteeElection;

use App\Entity\Committee;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_VOTER_IN_COMMITTEE', subject: 'committee')]
#[Route('/comites/{uuid}/listes-candidats', name: 'app_renaissance_committee_election_candidacies_lists_view', methods: ['GET'])]
class ViewCandidaciesListsController extends AbstractController
{
    public function __invoke(Committee $committee): Response
    {
        return $this->render('renaissance/committee_election/candidacies_lists.html.twig', [
            'committee' => $committee,
        ]);
    }
}
