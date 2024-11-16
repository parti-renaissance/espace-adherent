<?php

namespace App\Controller\Admin;

use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use App\Utils\HttpUtils;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        return HttpUtils::createResponse(
            $defaultStorage,
            $file->getPath(),
            \sprintf('%s.%s', Urlizer::urlize($file->getName()), $file->getExtension()),
            $file->getMimeType()
        );
    }

    #[IsGranted('ROLE_ADMIN_ADHERENT_ADHERENTS')]
    #[Route(path: '/fichiers/{filePath}', name: 'download_from_storage', requirements: ['filePath' => '/.*'], methods: ['GET'])]
    public function downloadFromStorageAction(string $filePath, FilesystemOperator $defaultStorage): Response
    {
        if (!$defaultStorage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this File.');
        }

        return HttpUtils::createResponse($defaultStorage, $filePath);
    }
}
