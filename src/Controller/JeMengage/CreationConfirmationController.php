<?php

namespace App\Controller\JeMengage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/bienvenue", name="app_jemengage_creation_confirmation", methods={"GET"})
 */
class CreationConfirmationController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('jemengage/user/creation_confirmation.html.twig');
    }
}
