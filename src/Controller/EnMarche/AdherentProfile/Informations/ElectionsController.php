<?php

namespace App\Controller\EnMarche\AdherentProfile\Informations;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections", name="app_adherent_profile_information_elections", methods={"GET"})
 */
class ElectionsController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/informations/elections.html.twig');
    }
}
