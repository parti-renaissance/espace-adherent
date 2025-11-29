<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Address\AddressInterface;
use App\Documents\DocumentManager;
use App\Documents\DocumentRepository;
use App\Entity\Adherent;
use League\Flysystem\FilesystemException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-adherent/documents')]
class DocumentsController extends AbstractController
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    #[Route(name: 'app_documents_index', methods: ['GET'])]
    public function indexAction(): Response
    {
        return $this->render('documents/index.html.twig', [
            'documents' => $this->documentManager->listAdherentFiles($this->getUser()),
        ]);
    }

    #[Route(path: '/dossier/{type}/{path}', requirements: ['type' => 'adherents|animateurs|referents|animateurs-etrangers', 'path' => '.+'], name: 'app_documents_directory', methods: ['GET'])]
    public function directoryAction(string $type, string $path): Response
    {
        $this->checkDocumentTypeAccess($type);

        return $this->render('documents/directory.html.twig', [
            'type' => $type,
            'path' => $path,
            'documents' => $this->documentManager->listDirectory($type, $path),
        ]);
    }

    #[Route(path: '/telecharger/{type}/{path}', requirements: ['type' => 'adherents|animateurs|referents|animateurs-etrangers', 'path' => '.+'], name: 'app_documents_file', methods: ['GET'])]
    public function fileAction(string $type, string $path): Response
    {
        $this->checkDocumentTypeAccess($type);

        try {
            $document = $this->documentManager->readDocument($type, $path);
        } catch (FilesystemException $e) {
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

        $isHost = $adherent->isHost() || $adherent->isSupervisor();

        if (DocumentRepository::DIRECTORY_HOSTS === $type && !$isHost) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_FOREIGN_HOSTS === $type && !($isHost || AddressInterface::FRANCE !== strtoupper($adherent->getCountry()))) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_REFERENTS === $type) {
            throw $this->createNotFoundException();
        }
    }
}
