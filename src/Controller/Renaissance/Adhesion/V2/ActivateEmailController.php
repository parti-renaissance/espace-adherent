<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v2/adhesion/activation', name: 'app_adhesion_activation', methods: ['GET', 'POST'])]
class ActivateEmailController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        return $this->render('renaissance/adhesion/activation.html.twig');
    }
}
