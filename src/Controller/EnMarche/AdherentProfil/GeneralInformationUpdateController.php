<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/general", name="app_adherent_profil_general_information_update", methods={"GET"})
 */
class GeneralInformationUpdateController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/general.html.twig');
    }
}
