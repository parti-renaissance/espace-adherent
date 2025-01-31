<?php

namespace App\Controller\Api\Team;

use App\Entity\Team\Team;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'team') and is_granted('SCOPE_CAN_MANAGE', subject)"), subject: 'team')]
#[Route(path: '/v3/teams/{uuid}', requirements: ['uuid' => '%pattern_uuid%'], name: 'api_team_remove', methods: ['DELETE'])]
class RemoveTeamController extends AbstractController
{
    public function __invoke(Team $team, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $entityManager->remove($team);
            $entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json([
                'title' => 'An error occurred',
                'detail' => 'Vous ne pouvez pas supprimer ce groupe car il est utilisé',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json('OK', Response::HTTP_OK);
    }
}
