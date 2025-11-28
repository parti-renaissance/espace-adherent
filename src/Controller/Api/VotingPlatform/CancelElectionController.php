<?php

declare(strict_types=1);

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
        if (!$designation->isFullyEditable()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Consultation is not editable',
            ], Response::HTTP_CONFLICT);
        }

        foreach ($electionRepository->findAllForDesignation($designation) as $election) {
            $election->cancel(ElectionCancelReasonEnum::Manual);
        }

        $designation->cancel();

        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
