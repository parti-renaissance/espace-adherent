<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\ReferentTagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ReferentTagController extends Controller
{
    /**
     * @Route("/referent-tag", name="api_referent_tag")
     * @Method("GET")
     */
    public function test(Request $request, ReferentTagRepository $repository, SerializerInterface $serializer): Response
    {
        $limit = $request->query->getInt('_per_page', 10);
        $offset = ($request->query->getInt('_page', 1) - 1) * $limit;

        return new JsonResponse(
            $serializer->serialize(
                $repository->findByPartialName($request->get('q'), $limit, $offset),
                'json',
                ['groups' => ['public']]
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
