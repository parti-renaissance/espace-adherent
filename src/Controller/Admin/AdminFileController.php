<?php

namespace App\Controller\Admin;

use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemOperator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/filesystem', name: 'app_admin_files_', methods: ['GET'])]
class AdminFileController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN_TERRITOIRES_FILES')]
    #[Route(path: '/file-directory/autocompletion', name: 'autocomplete_file_directory', methods: ['GET'], condition: 'request.isXmlHttpRequest()')]
    public function directoriesAutocompleteAction(Request $request, FileRepository $repository): JsonResponse
    {
        $directories = $repository->findForAutocomplete(
            mb_strtolower($request->query->get('term'))
        );

        return $this->json(
            $directories,
            Response::HTTP_OK,
            [],
            ['groups' => ['autocomplete']]
        );
    }

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_FILES')]
    #[Route(path: '/documents/{uuid}', name: 'download', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    public function downloadAction(File $file, FilesystemOperator $defaultStorage): Response
    {
        if ($file->isDir()) {
            throw $this->createNotFoundException('Directory cannot be download.');
        }

        if ($file->isLink()) {
            return $this->redirect($file->getExternalLink());
        }

        $filePath = $file->getPath();

        if (!$defaultStorage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this File.');
        }

        return $this->generateResponse($defaultStorage, $file->getPath(), $file->getMimeType(), $file->getName(), $file->getExtension());
    }

    #[IsGranted('ROLE_ADMIN_ADHERENT_ADHERENTS')]
    #[Route(path: '/fichiers/{filePath}', name: 'download_from_storage', requirements: ['filePath' => '/.*'], methods: ['GET'])]
    public function downloadFromStorageAction(string $filePath, FilesystemOperator $defaultStorage): Response
    {
        if (!$defaultStorage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this File.');
        }

        return $this->generateResponse($defaultStorage, $filePath, $defaultStorage->mimeType($filePath), pathinfo($filePath, \PATHINFO_FILENAME), pathinfo($filePath, \PATHINFO_EXTENSION));
    }

    private function generateResponse(FilesystemOperator $storage, string $filePath, string $mimeType, string $name, $extension): Response
    {
        $response = new Response($storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $mimeType,
        ]);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            \sprintf('%s.%s', Urlizer::urlize($name), $extension)
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
