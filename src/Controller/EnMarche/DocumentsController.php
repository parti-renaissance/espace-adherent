<?php

namespace App\Controller\EnMarche;

use App\Documents\DocumentRepository;
use App\Entity\Adherent;
use League\Flysystem\FileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-adherent/documents")
 */
class DocumentsController extends Controller
{
    /**
     * @Route(name="app_documents_index", methods={"GET"})
     */
    public function indexAction()
    {
        return $this->render('documents/index.html.twig', [
            'documents' => $this->get('app.document_manager')->listAdherentFiles($this->getUser()),
        ]);
    }

    /**
     * @Route(
     *     "/dossier/{type}/{path}",
     *     requirements={"type": "adherents|animateurs|referents|animateurs-etrangers", "path": ".+"},
     *     name="app_documents_directory",
     *     methods={"GET"}
     * )
     */
    public function directoryAction($type, $path)
    {
        $this->checkDocumentTypeAccess($type);

        return $this->render('documents/directory.html.twig', [
            'type' => $type,
            'path' => $path,
            'documents' => $this->get('app.document_manager')->listDirectory($type, $path),
        ]);
    }

    /**
     * @Route(
     *     "/telecharger/{type}/{path}",
     *     requirements={"type": "adherents|animateurs|referents|animateurs-etrangers", "path": ".+"},
     *     name="app_documents_file",
     *     methods={"GET"}
     * )
     */
    public function fileAction($type, $path)
    {
        $this->checkDocumentTypeAccess($type);

        try {
            $document = $this->get('app.document_manager')->readDocument($type, $path);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Document not found', $e);
        }

        $response = new Response($document['content']);
        $response->headers->set('Content-Type', $document['mimetype']);

        return $response;
    }

    private function checkDocumentTypeAccess(string $type): void
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $isHost = $adherent->isHost();
        $isReferent = $adherent->isReferent();

        if (DocumentRepository::DIRECTORY_HOSTS === $type && !($isHost || $isReferent)) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_FOREIGN_HOSTS === $type && !($isHost || $isReferent || 'FR' !== strtoupper($adherent->getCountry()))) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_REFERENTS === $type && !$isReferent) {
            throw $this->createNotFoundException();
        }
    }
}
