<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/communications", name="app_adherent_profil_subscriptions_update", methods={"GET"})
 */
class SubscriptionsPreferencesUpdateController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/subscriptions.html.twig');
    }
}
