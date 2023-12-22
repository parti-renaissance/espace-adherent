<?php

namespace App\Controller\Renaissance\Adhesion;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion', name: 'app_renaissance_adhesion', methods: ['GET|POST'])]
class AdhesionController extends AbstractAdhesionController
{
    public function __invoke(Request $request): Response
    {
        return $this->redirectToRoute('app_adhesion_index');
    }
}
