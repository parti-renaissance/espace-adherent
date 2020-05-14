<?php

namespace App\Controller\EnMarche\AdherentProfile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/centres-d-interet", name="app_adherent_profile_interests_update", methods={"GET"})
 */
class InterestsUpdateController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/interests.html.twig');
    }
}
