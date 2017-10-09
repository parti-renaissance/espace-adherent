<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserCanUnsubscribeController extends Controller
{
    /**
     * @Route("/can_unsubscribe/{uuid}", name="api_can_unsubscribe")
     * @Method("GET")
     */
    public function indexAction(Adherent $user)
    {
        return new JsonResponse(
            null,
            $user->isBasicAdherent() ? Response::HTTP_OK : Response::HTTP_FORBIDDEN
        );
    }
}
