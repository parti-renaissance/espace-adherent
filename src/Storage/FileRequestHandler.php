<?php

namespace App\Storage;

use App\Entity\EntityFileInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileRequestHandler
{
    public function __construct(
        private readonly FilesystemOperator $defaultStorage,
    ) {
    }

    public function createResponse(EntityFileInterface $file): Response
    {
        $response = new Response($this->defaultStorage->read($file->getPath()), Response::HTTP_OK, [
            'Content-Type' => $this->defaultStorage->mimeType($file->getPath()),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getSlug().'.'.$file->getExtension()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
