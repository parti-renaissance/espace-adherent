<?php

namespace App\Controller\Api\Team;

use App\Entity\Team\Team;
use App\Team\TeamMemberManagementHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/v3/teams/{uuid}/add-members", requirements={"uuid": "%pattern_uuid%"}, name="api_team_add_members", methods={"PUT"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'team')")
 */
class AddTeamMembersController extends AbstractController
{
    public function __invoke(
        Request $request,
        Team $team,
        TeamMemberManagementHandler $teamMemberManagementHandler,
        SerializerInterface $serializer
    ): JsonResponse {
        $newMembers = $serializer->deserialize($request->getContent(), 'App\Api\DTO\AdherentUuid[]', JsonEncoder::FORMAT);

        $teamMemberManagementHandler->handleMembersToAdd($team, $newMembers);
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
