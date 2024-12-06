<?php

namespace App\Controller\EnMarche\Filesystem;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use App\Utils\HttpUtils;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

abstract class AbstractFilesController extends AbstractController
{
    use AccessDelegatorTrait;

    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    #[IsGranted('CAN_DOWNLOAD_FILE', subject: 'file')]
    #[Route(path: '/documents/{uuid}', name: 'download', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%'])]
    public function downloadAction(File $file, FilesystemOperator $defaultStorage): Response
    {
        if ($file->isDir()) {
            throw $this->createNotFoundException('Directory cannot be download.');
        }

        if ($file->isLink()) {
            return $this->redirect($file->getExternalLink());
        }

        $filePath = $file->getPath();

        return HttpUtils::createResponse(
            $defaultStorage,
            $filePath,
            $this->getFilenameForDownload($file),
            $file->getMimeType()
        );
    }

    #[Route(path: '/documents', name: 'list', methods: ['GET'])]
    #[Route(path: '/documents/{slug}', name: 'list_in_directory', methods: ['GET'], requirements: ['slug' => '[A-Za-z0-9\-]+'])]
    public function listAction(Request $request, ?File $directory = null): Response
    {
        $order = $request->query->get('order', 'a');
        $files = $this->fileRepository->findWithPermissionsInDirectory($this->getMainUser($request->getSession())->getFilePermissions(), $directory, $order);

        return $this->renderTemplate('filesystem/list.html.twig', [
            'files' => $files,
            'directory' => $directory,
            'order' => 'd' === $order ? 'a' : 'd',
        ]);
    }

    abstract protected function getSpaceType(): string;

    private function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('filesystem/_base_%s_space.html.twig', $spaceName = $this->getSpaceType()),
                'space_name' => $spaceName,
            ]
        ));
    }

    private function getFilenameForDownload(File $file): ?string
    {
        return \sprintf('%s.%s', Urlizer::urlize($file->getName()), $file->getExtension());
    }
}
