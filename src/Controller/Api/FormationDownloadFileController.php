<?php

namespace App\Controller\Api;

use App\Entity\AdherentFormation\Formation;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FormationDownloadFileController extends AbstractController
{
    public function __construct(private readonly FilesystemOperator $defaultStorage, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, Formation $formation): Response
    {
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
}
