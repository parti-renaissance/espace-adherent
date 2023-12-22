<?php

namespace App\Controller\Renaissance\Adhesion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion/fin', name: 'app_renaissance_adhesion_finish', methods: ['GET'])]
class FinishController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/adhesion/end_adhesion.html.twig');
    }
}
