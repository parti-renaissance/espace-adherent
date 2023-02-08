<?php

namespace App\Controller\Api\Formation;

use App\Entity\AdherentFormation\Formation;
use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FormationDownloadFileController extends AbstractController
{
    public function __invoke(Request $request, Formation $formation, FilesystemInterface $storage): Response
    {
        $filePath = $formation->getFilePath();

        if (!$storage->has($filePath)) {
            throw $this->createNotFoundException('File not found.');
        }

        $response = new Response($storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $storage->getMimetype($filePath),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            (new Slugify())->slugify($formation->getTitle()).'.'.pathinfo($filePath, \PATHINFO_EXTENSION)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
