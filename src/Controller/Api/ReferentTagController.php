<?php

namespace App\Controller\Api;

use App\Entity\ReferentTag;
use App\Repository\ReferentTagRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReferentTagController extends Controller
{
    /**
     * @Route("/referent-tags", name="api_referent_tag", methods={"GET"})
     */
    public function getReferentTagsAction(
        Request $request,
        ReferentTagRepository $repository,
        SerializerInterface $serializer
    ): Response {
        $limit = $request->query->getInt('_per_page', 10);
        $offset = ($request->query->getInt('_page', 1) - 1) * $limit;

        return new JsonResponse(
            ['items' => array_map(
                function (ReferentTag $tag) {
                    return [
                        'id' => $tag->getCode(),
                        'label' => sprintf('%s - %s', $tag->getName(), $tag->getCode()),
                    ];
                },
                $repository->findByPartialName($request->get('q'), $limit, $offset)
            )]
        );
    }
}
