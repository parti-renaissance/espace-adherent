<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/activité", name="app_adherent_profil_activity", methods={"GET"})
 */
class ActivityController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/activity.html.twig');
    }
}
