<?php

namespace App\Controller\Api;

use App\Entity\TurnkeyProject;
use App\Repository\TurnkeyProjectRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/turnkey-projects")
 */
class TurnkeyProjectController extends Controller
{
    /**
     * @Route(name="api_approved_turnkey_projects", methods={"GET"})
     */
    public function getApprovedTurnkeyProjectAction(
        TurnkeyProjectRepository $repository,
        Serializer $serializer
    ): Response {
        return new JsonResponse(
            $serializer->serialize($repository->findApprovedOrdered(), 'json', SerializationContext::create()->setGroups(['turnkey_project_list'])),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/count", name="api_count_approved_turnkey_projects", methods={"GET"})
     */
    public function countApprovedTurnkeyProjectAction(TurnkeyProjectRepository $repository): Response
    {
        return new JsonResponse(
            ['total' => $repository->countApproved()],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * @Route("/pinned", name="api_pinned_turnkey_project", methods={"GET"})
     */
    public function getPinnedTurnkeyProjectAction(
        TurnkeyProjectRepository $repository,
        Serializer $serializer
    ): Response {
        return new JsonResponse(
            $serializer->serialize($repository->findPinned(), 'json', SerializationContext::create()->setGroups(['turnkey_project_read'])),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/{slug}", name="api_turnkey_project", methods={"GET"})
     * @Entity("turnkeyProject", expr="repository.findOneApprovedBySlug(slug)")
     */
    public function getTurnkeyProjectAction(TurnkeyProject $turnkeyProject, Serializer $serializer): Response
    {
        return new JsonResponse(
            $serializer->serialize($turnkeyProject, 'json', SerializationContext::create()->setGroups(['turnkey_project_read'])),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
