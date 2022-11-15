<?php

namespace App\Controller\EnMarche;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/paremetres/profil-renaissance", name="app_renaissance_profile", methods={"GET"})
 */
class RenaissanceProfileController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent/renaissance_profile.html.twig');
    }
}
