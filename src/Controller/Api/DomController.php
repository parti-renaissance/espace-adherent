<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DomController extends Controller
{
    /**
     * @Route("/dom", name="app_api_dom")
     * @Method("GET")
     */
    public function dom(): JsonResponse
    {
        return new JsonResponse([
            'header' => $this->renderView('components/_header.html.twig'),
            'footer' => $this->renderView('components/_footer.html.twig'),
        ]);
    }
}
