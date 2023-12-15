<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v2/adhesion/felicitations', name: 'app_adhesion_finish', methods: ['GET'])]
class FinishController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/adhesion/finish.html.twig', [
            'name' => 'John',
        ]);
    }
}
