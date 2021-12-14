<?php

namespace App\Controller\Api\Team;

use App\Api\DTO\AdherentUuid;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/v3/teams/{uuid}/add-members", requirements={"uuid": "%pattern_uuid%"}, name="api_team_add_members", methods={"PUT"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'team') and is_granted('CAN_EDIT_TEAM', team)")
 */
class AddTeamMembersController extends AbstractController
{
    public function __invoke(
        Request $request,
        Team $team,
        TeamMemberManagementHandler $teamMemberManagementHandler,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $newMembers = $serializer->deserialize($request->getContent(), AdherentUuid::class.'[]', JsonEncoder::FORMAT);

        if (\count($newMembers) < 1) {
            return $this->json('Vous devez fournir l\'id d\'au moins un membre.', Response::HTTP_BAD_REQUEST);
        }

        if (($errors = $validator->validate($newMembers))->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $teamMemberManagementHandler->handleMembersToAdd($team, $newMembers);
        $team->reorderMembersCollection();

        return $this->json($team, Response::HTTP_OK, [], ['groups' => ['team_read']]);
    }
}
