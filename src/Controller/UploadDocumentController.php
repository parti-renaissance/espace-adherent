<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserDocument;
use AppBundle\UserDocument\UserDocumentManager;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use League\Flysystem\FileNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UploadDocumentController extends Controller
{
    /**
     * @Route("/upload/{type}", defaults={"_enable_campaign_silence"=true}, name="app_filebrowser_upload")
     * @Method("POST")
     */
    public function filebrowserUploadAction(string $type, Request $request)
    {
        if (!in_array($type, UserDocument::ALL_TYPES)) {
            throw new NotFoundHttpException("File upload is not defined for type '$type'.");
        }

        if (!$request->query->has('CKEditorFuncNum') || 0 == $request->files->count()) {
            throw new BadRequestHttpException("Request parameter 'CKEditorFuncNum' needed.");
        }

        $message = $this->get('translator')->trans('document.upload.success');
        try {
            $document = $this->get(UserDocumentManager::class)->createAndSave($request->files->get('upload'), $type);
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
     * @Route("/documents-partages/{uuid}/{filename}", requirements={"uuid": "%pattern_uuid%"}, defaults={"_enable_campaign_silence"=true}, name="app_download_user_document")
     * @Method("GET")
     */
    public function downloadDocumentAction(UserDocument $document, string $filename, Request $request)
    {
        if ($filename !== $document->getOriginalName()) {
            throw $this->createNotFoundException('Document not found', "The requested file with name '$filename' doesn't match the identifier.");
        }

        try {
            $documentContent = $this->get(UserDocumentManager::class)->getContent($document);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Document not found', $e);
        }

        return new SnappyResponse($documentContent, $filename, $document->getMimeType());
    }
}
