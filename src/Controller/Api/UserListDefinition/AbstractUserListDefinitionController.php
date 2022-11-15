<?php

namespace App\Controller\Api\UserListDefinition;

use App\UserListDefinition\UserListDefinitionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractUserListDefinitionController extends AbstractController
{
    /**
     * @Route("/members/save",
     *     name="save_user_list_definition_members_for_type",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"POST"}
     * )
     */
    public function saveUserListDefinitionMembersForType(
        Request $request,
        UserListDefinitionManager $userListDefinitionManager
    ): Response {
        if (!$members = $request->request->get('members')) {
            return $this->json('"members" not provided', Response::HTTP_BAD_REQUEST);
        }

        try {
            $userListDefinitionManager->updateUserListDefinitionMembers($members, $this->getMemberEntityClass());
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_OK);
        }

        return $this->json('', Response::HTTP_OK);
    }

    /**
     * @Route("/{type}/members",
     *     name="get_user_list_definition_members_for_type",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"POST"}
     * )
     *
     * @IsGranted("ABLE_TO_MANAGE_USER_LIST_DEFINITION_TYPE", subject="type")
     */
    public function getUserListDefinitionMembersForType(
        Request $request,
        string $type,
        UserListDefinitionManager $userListDefinitionManager
    ): JsonResponse {
        if (!$ids = $request->request->get('ids')) {
            return $this->json('"ids" not provided', Response::HTTP_BAD_REQUEST);
        }

        $members = $userListDefinitionManager->getUserListDefinitionMembers(
            $type,
            $ids,
            $this->getMemberEntityClass()
        );

        return $this->json($members, Response::HTTP_OK);
    }

    abstract protected function getMemberEntityClass(): string;
}
