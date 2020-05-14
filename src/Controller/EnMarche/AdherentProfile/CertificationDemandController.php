<?php

namespace App\Controller\EnMarche\AdherentProfile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/informations/certification", name="app_adherent_profile_certification_demand", methods={"GET"})
 */
class CertificationDemandController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/certification.html.twig');
    }
}
