<?php

namespace AppBundle\Controller\EnMarche\AdherentProfil;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mes-documents", name="app_adherent_profil_documents", methods={"GET"})
 */
class DocumentsController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('adherent_profile/documents.html.twig');
    }
}
