<?php

namespace App\Controller\Renaissance\Formation;

use App\Controller\CanaryControllerTrait;
use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Storage\FileRequestHandler;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/formations/{uuid}/contenu", name="app_renaissance_adherent_formation_content", methods={"GET"})
 * @Entity("formation", expr="repository.findOnePublished(uuid)")
 * @IsGranted("RENAISSANCE_ADHERENT")
 */
class ContentController extends AbstractController
{
    use CanaryControllerTrait;

    public function __construct(
        private readonly FileRequestHandler $fileRequestHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemInterface $storage
    ) {
    }

    public function __invoke(Formation $formation): Response
    {
        $this->disableInProduction();

        $formation->incrementPrintCount();
        $this->entityManager->flush();

        if ($formation->isFileContent()) {
            $filePath = $formation->getFilePath();

            if (!$this->storage->has($filePath)) {
                throw $this->createNotFoundException('File not found.');
            }

            $response = new Response($this->storage->read($filePath), Response::HTTP_OK, [
                'Content-Type' => $this->storage->getMimetype($filePath),
            ]);

            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                (new Slugify())->slugify($formation->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
            );

            $response->headers->set('Content-Disposition', $disposition);

            return $response;
        }

        if ($formation->isLinkContent()) {
            return $this->redirect($formation->getLink());
        }

        throw $this->createNotFoundException(sprintf('No content found for Formation "%s".', $formation->getUuid()));
    }
}
