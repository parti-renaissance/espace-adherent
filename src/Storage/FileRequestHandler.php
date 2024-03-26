<?php

namespace App\Storage;

use App\Entity\EntityFileInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * TODO: remove
 */
class FileRequestHandler
{
    private $storage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    public function createResponse(EntityFileInterface $file): Response
    {
        $response = new Response($this->storage->read($file->getPath()), Response::HTTP_OK, [
            'Content-Type' => $this->storage->getMimetype($file->getPath()),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getSlug().'.'.$file->getExtension()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
