<?php

namespace AppBundle\Controller\Api;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @Route("/me", name="app_api_user_show_me")
     * @Method("GET")
     */
    public function showMeAction(Serializer $serializer): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize(
                $this->getUser(),
                'json',
                SerializationContext::create()->setGroups(['user_profile'])
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}
