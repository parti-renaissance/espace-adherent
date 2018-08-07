<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\TurnkeyProject;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TurnkeyProjectController extends Controller
{
    /**
     * @Route("/turnkey-project/{slug}", name="api_turnkey_project")
     * @Method("GET")
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
