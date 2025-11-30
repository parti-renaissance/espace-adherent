<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Document;
use App\Utils\HttpUtils;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        return HttpUtils::createResponse(
            $this->defaultStorage,
            $document->filePath,
            new Slugify()->slugify($document->title).'.'.pathinfo($document->filePath, \PATHINFO_EXTENSION)
        );
    }
}
