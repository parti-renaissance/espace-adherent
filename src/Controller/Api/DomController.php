<?php

namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DomController extends Controller
{
    /**
     * @Route("/dom", name="app_api_dom", methods={"GET"})
     */
    public function dom(): JsonResponse
    {
        return new JsonResponse([
            'header' => $this->renderView('components/_header.html.twig'),
            'footer' => $this->renderView('components/_footer.html.twig'),
        ]);
    }
}
