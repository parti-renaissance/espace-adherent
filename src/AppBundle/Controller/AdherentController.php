<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/espace-adherent")
 */
class AdherentController extends Controller
{
    /**
     * @Route("/mon-profil", name="app_adherent_profile")
     */
    public function profileAction()
    {
        return $this->render('adherent/profile.html.twig');
    }
}
