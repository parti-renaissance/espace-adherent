<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum;
use AppBundle\Repository\IdeaRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/ideas-workshop")
 */
class IdeasWorkshopController extends Controller
{
    /**
     * @Route("/idea", name="api_ideas_list")
     * @Method("GET")
     */
    public function ideaListAction(
        Request $request,
        IdeaRepository $ideaRepository,
        Serializer $serializer
    ): Response {
        $limit = $request->query->getInt('_per_page', 8);

        return new JsonResponse(
            $serializer->serialize(
                $ideaRepository->findIdeasByStatusThemeCategoryAndName(
                    $limit,
                    ($request->query->getInt('_page', 1) - 1) * $limit,
                    $request->query->get('status', IdeaStatusEnum::PUBLISHED),
                    $request->query->get('name'),
                    $request->query->get('theme'),
                    $request->query->get('category')
                ),
                'json',
                SerializationContext::create()->setGroups('idea_list')
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
