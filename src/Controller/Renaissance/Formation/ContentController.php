<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Formation;

use App\Entity\Adherent;
use App\Entity\AdherentFormation\Formation;
use App\Utils\HttpUtils;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('RENAISSANCE_ADHERENT')]
#[Route(path: '/espace-adherent/formations/{uuid}/contenu', name: 'app_renaissance_adherent_formation_content', methods: ['GET'])]
class ContentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator $defaultStorage,
    ) {
    }

    public function __invoke(
        #[MapEntity(expr: 'repository.findOnePublished(uuid)')]
        Formation $formation,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if ($formation->addPrintByAdherent($adherent)) {
            $this->entityManager->flush();
        }

        if ($formation->isFileContent()) {
            $filePath = $formation->getFilePath();

            return HttpUtils::createResponse(
                $this->defaultStorage,
                $filePath,
                new Slugify()->slugify($formation->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
            );
        }

        if ($formation->isLinkContent()) {
            return $this->redirect($formation->getLink());
        }

        throw $this->createNotFoundException(\sprintf('No content found for Formation "%s".', $formation->getUuid()));
    }
}
