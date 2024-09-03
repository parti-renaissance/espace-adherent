<?php

namespace App\Controller\Api\VotingPlatform;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Election\Enum\ElectionCancelReasonEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CancelElectionController extends AbstractController
{
    public function __invoke(
        Designation $designation,
        EntityManagerInterface $entityManager,
        ElectionRepository $electionRepository,
    ): Response {
        foreach ($electionRepository->findAllForDesignation($designation) as $election) {
            $election->cancel(ElectionCancelReasonEnum::Manual);
        }

        $designation->cancel();

        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
