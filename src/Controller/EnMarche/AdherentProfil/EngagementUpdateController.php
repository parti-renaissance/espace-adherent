<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/engagement", name="app_adherent_profil_engagement_update", methods={"GET"})
 */
class EngagementUpdateController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/engagement.html.twig');
    }
}
