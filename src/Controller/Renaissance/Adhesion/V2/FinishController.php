<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/felicitations', name: 'app_adhesion_finish', methods: ['GET'])]
class FinishController extends AbstractController
{
    public function __invoke(): Response
    {
        if (!$this->getUser() instanceof Adherent) {
            return $this->redirectToRoute('app_adhesion_index');
        }

        return $this->render('renaissance/adhesion/finish.html.twig');
    }
}
