<?php

namespace App\Controller\Api\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\Repository\VotingPlatform\VoterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMENGAGE_ADMIN') and is_granted('IS_FEATURE_GRANTED', 'designation')"))]
#[Route('/v3/designations/{uuid}/voters', name: 'app_designation_get_voters', methods: ['GET'])]
class ElectionVotersListController extends AbstractController
{
    public function __invoke(
        Designation $designation,
        ElectionRepository $electionRepository,
        VoterRepository $voterRepository,
    ): Response {
        if (!$election = $electionRepository->findOneByDesignation($designation)) {
            return $this->json([]);
        }

        return $this->json($voterRepository->getVotersForElection($election));
    }
}
