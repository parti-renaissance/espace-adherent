<?php

namespace App\Controller\Renaissance\Formation;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Entity('formation', expr: 'repository.findOnePublished(uuid)')]
#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/espace-adherent/formations/{uuid}/contenu', name: 'app_renaissance_adherent_formation_content', methods: ['GET'])]
class ContentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator $defaultStorage,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(Formation $formation): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($formation->addPrintByAdherent($adherent)) {
            $this->entityManager->flush();
        }

        if ($formation->isFileContent()) {
            $filePath = $formation->getFilePath();

            if (!$this->defaultStorage->has($filePath)) {
                $this->logger->error(\sprintf('No file found for Formation with uuid "%s".', $formation->getUuid()->toString()));

                throw $this->createNotFoundException('File not found.');
            }

            $response = new Response($this->defaultStorage->read($filePath), Response::HTTP_OK, [
                'Content-Type' => $this->defaultStorage->mimeType($filePath),
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

        throw $this->createNotFoundException(\sprintf('No content found for Formation "%s".', $formation->getUuid()));
    }
}
