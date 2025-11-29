<?php

declare(strict_types=1);

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'designation')"))]
#[Route('/v3/designations/{uuid}/results', name: 'app_designation_get_results', methods: ['GET'])]
class ElectionResultsController extends AbstractController
{
    public function __invoke(Designation $designation, ElectionRepository $electionRepository): Response
    {
        if (!$election = $electionRepository->findOneByDesignation($designation)) {
            return $this->json([]);
        }

        if (!$election->hasResult()) {
            return $this->json($electionRepository->getLiveResults($election->getCurrentRound()));
        }

        $electionResult = $election->getElectionResult();

        return $this->json($electionResult->getElectionRoundResult($election->getCurrentRound())->getElectionPoolResults(), context: ['groups' => ['election_result']]);
    }
}
