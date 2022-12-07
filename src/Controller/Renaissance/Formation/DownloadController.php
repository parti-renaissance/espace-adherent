<?php

namespace App\Controller\Renaissance\Formation;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Storage\FileRequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/espace-adherent/formations/{id}/telecharger", name="app_renaissance_adherent_formation_download", methods={"GET"})
 */
class DownloadController extends AbstractController
{
    public function __construct(
        private readonly FileRequestHandler $fileRequestHandler,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Formation $formation): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!$adherent->isRenaissanceUser()) {
            return $this->redirect($this->generateUrl('app_renaissance_homepage', [], UrlGeneratorInterface::ABSOLUTE_URL));
        }

        if (!$formation->isVisible()) {
            throw $this->createNotFoundException();
        }

        $formation->incrementDownloadsCount();
        $this->entityManager->flush();

        return $this->fileRequestHandler->createResponse($formation->getFile());
    }
}
