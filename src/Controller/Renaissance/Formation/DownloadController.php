<?php

namespace App\Controller\Renaissance\Formation;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Storage\FileRequestHandler;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/formations/{id}/telecharger", name="app_renaissance_adherent_formation_download", methods={"GET"})
 * @Entity("formation", expr="repository.findOneVisible(id)")
 * @IsGranted("RENAISSANCE_ADHERENT")
 */
class DownloadController extends AbstractController
{
    use AdherentFormationControllerTrait;

    public function __construct(
        private readonly FileRequestHandler $fileRequestHandler,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(Formation $formation): Response
    {
        $this->checkAdherentFormationsEnabled();

        $formation->incrementDownloadsCount();
        $this->entityManager->flush();

        return $this->fileRequestHandler->createResponse($formation->getFile());
    }
}
