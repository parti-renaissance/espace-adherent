<?php

namespace App\Controller\EnMarche\Filesystem;

use App\Controller\EntityControllerTrait;
use App\Entity\Filesystem\File;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/documents", name="app_files_")
 *
 * @Security("is_granted('ROLE_ADHERENT') or is_granted('ROLE_ADMIN_FILES')")
 */
class FileController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/{uuid}", name="download", methods={"GET"})
     * @Security("is_granted('CAN_DOWNLOAD_FILE', file) or is_granted('ROLE_ADMIN_FILES')")
     */
    public function downloadAction(File $file, FilesystemInterface $storage): Response
    {
        if ($file->isDir()) {
            throw $this->createNotFoundException('Directory cannot be download.');
        }

        if ($file->isLink()) {
            return $this->redirect($file->getExternalLink());
        }

        $filePath = $file->getPath();

        if (!$storage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this File.');
        }

        $response = new Response($storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $file->getMimeType(),
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->getFilenameForDownload($file)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function getFilenameForDownload(File $file): ?string
    {
        return \sprintf('%s.%s', Urlizer::urlize($file->getName()), $file->getExtension());
    }
}
