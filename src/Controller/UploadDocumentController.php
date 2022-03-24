<?php

namespace App\Controller;

use App\Entity\UserDocument;
use App\UserDocument\UserDocumentManager;
use Gedmo\Sluggable\Util\Urlizer;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use League\Flysystem\FileNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadDocumentController extends AbstractController
{
    /**
     * @Route("/upload/{type}", name="app_filebrowser_upload", methods={"POST"})
     *
     * @Security("is_granted('FILE_UPLOAD', type)")
     */
    public function filebrowserUploadAction(
        string $type,
        Request $request,
        UserDocumentManager $manager,
        TranslatorInterface $translator
    ): Response {
        if (!\in_array($type, UserDocument::ALL_TYPES)) {
            throw new NotFoundHttpException("File upload is not defined for type '$type'.");
        }

        if (0 === $request->files->count()) {
            throw new BadRequestHttpException('Uploaded file not provided.');
        }

        if (!$request->query->has('CKEditorFuncNum')) {
            throw new BadRequestHttpException("Request parameter 'CKEditorFuncNum' needed.");
        }

        $message = $translator->trans('document.upload.success');
        try {
            $document = $manager->createAndSave($request->files->get('upload'), $type);
            $url = $this->generateUrl('app_download_user_document', ['uuid' => $document->getUuid()->toString(), 'filename' => $document->getOriginalName()], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $e) {
            $url = '';
            $message = $e->getMessage();
        }

        return $this->render('for_filebrowser_ckeditor.html.twig', [
            'funcNum' => $request->query->get('CKEditorFuncNum'),
            'url' => $url,
            'message' => $message,
        ]);
    }

    /**
     * @Route("/api/v3/upload/{type}", name="api_filebrowser_upload_v3", methods={"POST"})
     *
     * @Security("is_granted('FILE_UPLOAD', type)")
     */
    public function filebrowserUploadForApi(string $type, Request $request, UserDocumentManager $manager): Response
    {
        if (!\in_array($type, UserDocument::ALL_TYPES)) {
            return $this->json(
                ['message' => "Téléchargement de fichier n'est pas autorisé pour le type '$type'."],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (0 === $request->files->count()) {
            return $this->json(
                ['message' => 'Aucun document uploadé.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $message = 'Le document a été uploadé avec succès.';
        try {
            $document = $manager->createAndSave($request->files->get('upload'), $type);
            $url = $this->generateUrl('app_download_user_document', ['uuid' => $document->getUuid()->toString(), 'filename' => $document->getOriginalName()], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $e) {
            $url = '';
            $message = $e->getMessage();
        }

        return $this->json(['url' => $url, 'message' => $message], Response::HTTP_OK);
    }

    /**
     * @Route("/ck-upload/{type}", name="app_filebrowser_upload_ckeditor5", methods={"POST"})
     * @Security("is_granted('FILE_UPLOAD', type)")
     */
    public function ckFileUploadAction(string $type, Request $request, UserDocumentManager $manager): Response
    {
        if (!\in_array($type, UserDocument::ALL_TYPES)) {
            throw new NotFoundHttpException("File upload is not defined for type '$type'.");
        }

        if (!$request->files->count()) {
            throw new BadRequestHttpException();
        }

        try {
            $document = $manager->createAndSave($request->files->get('upload'), $type);
            $url = $this->generateUrl('app_download_user_document', ['uuid' => $document->getUuid()->toString(), 'filename' => $document->getOriginalName()], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (\Exception $e) {
            $url = '';
        }

        return $this->json([
            'uploaded' => (bool) $url,
            'url' => $url,
        ]);
    }

    /**
     * @Route("/documents-partages/{uuid}/{filename}", requirements={"uuid": "%pattern_uuid%"}, name="app_download_user_document", methods={"GET"})
     */
    public function downloadDocumentAction(
        UserDocument $document,
        string $filename,
        UserDocumentManager $manager
    ): Response {
        if ($filename !== $document->getOriginalName()) {
            throw $this->createNotFoundException('Document not found');
        }

        try {
            $documentContent = $manager->getContent($document);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Document not found', $e);
        }

        return new SnappyResponse(
            $documentContent,
            sprintf(
                '%s.%s',
                Urlizer::urlize($document->getFilename()),
                $document->getExtension()
            ),
            $document->getMimeType()
        );
    }
}
