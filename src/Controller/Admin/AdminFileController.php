<?php

namespace App\Controller\Admin;

use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/filesystem", name="app_admin_files_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_ADMIN_FILES')")
 */
class AdminFileController extends Controller
{
    /**
     * @Route("/file-directory/autocompletion",
     *     name="autocomplete_file_directory",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
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

    /**
     * @Route("/documents/{uuid}", name="download", methods={"GET"}, requirements={"uuid": "%pattern_uuid%"})
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
            \sprintf('%s.%s', Urlizer::urlize($file->getName()), $file->getExtension())
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
