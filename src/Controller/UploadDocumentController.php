<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\UserDocument;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\FileUploadVoter;
use App\UserDocument\UserDocumentManager;
use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadDocumentController extends AbstractController
{
    #[IsGranted(FileUploadVoter::FILE_UPLOAD, subject: 'type')]
    #[Route(path: '/api/v3/upload/{type}', name: 'api_filebrowser_upload_v3', methods: ['POST'])]
    public function filebrowserUploadForApi(string $type, Request $request, UserDocumentManager $manager, ScopeGeneratorResolver $scopeGeneratorResolver): Response
    {
        if (!\in_array($type, UserDocument::ALL_TYPES)) {
            return $this->json(
                ['message' => "Téléchargement de fichier n'est pas autorisé pour le type '$type'."],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (0 === $request->files->count() || !$file = $request->files->get('upload')) {
            return $this->json(
                ['message' => 'Aucun document téléchargé ou son poids dépasse la limite autorisée (100 Mo).'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $message = 'Le document a été téléchargé avec succès.';
        try {
            $document = $manager->createAndSave($file, $type, $scopeGeneratorResolver->generate());
            $url = $this->generateUrl('app_download_user_document', ['uuid' => $document->getUuid()->toString(), 'filename' => $document->getOriginalName()], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $e) {
            $url = '';
            $message = $e->getMessage();
        }

        return $this->json(['url' => $url, 'message' => $message], Response::HTTP_OK);
    }

    #[Route('/documents-partages/{uuid}/{filename}', name: 'app_download_user_document', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    public function downloadDocumentAction(
        UserDocument $document,
        string $filename,
        UserDocumentManager $manager,
        MimeTypesInterface $mimeTypes,
    ): Response {
        if ($filename !== $document->getOriginalName()) {
            throw $this->createNotFoundException('Document not found');
        }

        try {
            $documentContent = $manager->getContent($document);
        } catch (FilesystemException $e) {
            throw $this->createNotFoundException('Document not found', $e);
        }

        $mimeType = $mimeTypes->getMimeTypes($document->getExtension())[0] ?? 'application/octet-stream';

        $dispositionType = \in_array($mimeType, ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'], true)
            ? HeaderUtils::DISPOSITION_INLINE
            : HeaderUtils::DISPOSITION_ATTACHMENT;

        $response = new Response($documentContent);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            $dispositionType,
            \sprintf('%s.%s', Urlizer::urlize($document->getFilename()), $document->getExtension())
        ));

        return $response;
    }
}
