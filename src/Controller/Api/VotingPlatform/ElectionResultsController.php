<?php

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v3/designations/{uuid}/results', name: 'app_designation_get_results', methods: ['GET'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')")]
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
