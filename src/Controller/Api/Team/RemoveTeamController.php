<?php

namespace App\Controller\Api\Team;

use App\Entity\Team\Team;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/teams/{uuid}', requirements: ['uuid' => '%pattern_uuid%'], name: 'api_team_remove', methods: ['DELETE'])]
#[Security("is_granted('IS_FEATURE_GRANTED', 'team') and is_granted('SCOPE_CAN_MANAGE', team)")]
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
