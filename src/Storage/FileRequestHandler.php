<?php

namespace App\Storage;

use App\Entity\EntityFileInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileRequestHandler
{
    private $filesystem;

    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function createResponse(EntityFileInterface $file): Response
    {
        $response = new Response($this->filesystem->read($file->getPath()), Response::HTTP_OK, [
            'Content-Type' => $this->filesystem->getMimetype($file->getPath()),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $file->getSlug().'.'.$file->getExtension()
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
