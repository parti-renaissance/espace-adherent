<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    /**
     * @Route("/mon-profil", name="app_adherent_profile")
     */
    public function profileAction(): Response
    {
        return $this->render('adherent/profile.html.twig');
    }

    /**
     * This action enables a new user to pin his/her interests.
     *
     * @Route("/centres-interets", name="app_adherent_pin_interests")
     */
    public function pinInterestsAction(Request $request): Response
    {
        // User may not be activated, if so its ID is in the session
        // see registerAction above.
        return new Response('TO BE IMPLEMENTED');
    }
}
