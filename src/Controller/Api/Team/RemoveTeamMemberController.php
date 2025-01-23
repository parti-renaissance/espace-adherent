<?php

namespace App\Controller\Api\Team;

use App\Entity\Adherent;
use App\Entity\Team\Team;
use App\Team\TeamMemberManagementHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Entity('adherent', expr: 'repository.findOneByUuid(adherent_uuid)')]
#[Route(path: '/v3/teams/{uuid}/members/{adherent_uuid}', requirements: ['uuid' => '%pattern_uuid%', 'adherent_uuid' => '%pattern_uuid%'], name: 'api_team_remove_member', methods: ['DELETE'])]
#[Security("is_granted('REQUEST_SCOPE_GRANTED', 'team') and is_granted('SCOPE_CAN_MANAGE', team)")]
class RemoveTeamMemberController extends AbstractController
{
    public function __invoke(
        Team $team,
        Adherent $adherent,
        TeamMemberManagementHandler $teamMemberManagementHandler,
    ): JsonResponse {
        $teamMemberManagementHandler->handleMemberToRemove($team, $adherent);
        $team->reorderMembersCollection();

        return $this->json(
            $team,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['team_read'],
            ]
        );
    }
}
