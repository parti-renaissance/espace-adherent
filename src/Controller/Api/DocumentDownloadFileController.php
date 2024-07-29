<?php

namespace App\Controller\Api;

use App\Entity\Document;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DocumentDownloadFileController extends AbstractController
{
    public function __construct(private readonly FilesystemOperator $defaultStorage, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, Document $document): Response
    {
        if (!$document->hasFilePath() || !$this->defaultStorage->has($document->filePath)) {
            $this->logger->error(\sprintf('No file found for Document with uuid "%s".', $document->getUuid()->toString()));

            throw $this->createNotFoundException('File not found.');
        }

        $response = new Response($this->defaultStorage->read($document->filePath), Response::HTTP_OK, [
            'Content-Type' => $this->defaultStorage->mimeType($document->filePath),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (new Slugify())->slugify($document->title).'.'.pathinfo($document->filePath, \PATHINFO_EXTENSION)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
