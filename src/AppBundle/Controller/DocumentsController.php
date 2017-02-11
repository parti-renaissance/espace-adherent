<?php

namespace AppBundle\Controller;

use AppBundle\Documents\DocumentRepository;
use League\Flysystem\FileNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-adherent/documents")
 */
class DocumentsController extends Controller
{
    /**
     * @Route(name="app_documents_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $documents = [];
        $documents['adherent'] = $this->get('app.documents_repository')->listAdherentDirectory('/');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_REFERENT')) {
            $documents['referent'] = $this->get('app.documents_repository')->listReferentDirectory('/');
        }

        if ($this->get('app.committee_manager')->isCommitteeHost($this->getUser())) {
            $documents['host'] = $this->get('app.documents_repository')->listHostDirectory('/');
        }

        return $this->render('documents/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    /**
     * @Route(
     *     "/dossier/{type}/{path}",
     *     requirements={"type"="adherents|animateurs|referents", "path"=".+"},
     *     name="app_documents_directory"
     * )
     * @Method("GET")
     */
    public function directoryAction($type, $path)
    {
        $this->checkDocumentTypeAccess($type);

        return $this->render('documents/directory.html.twig', [
            'type' => $type,
            'path' => $path,
            'documents' => $this->get('app.documents_repository')->listDirectory($type, $path),
        ]);
    }

    /**
     * @Route(
     *     "/telecharger/{type}/{path}",
     *     requirements={"type"="adherents|animateurs|referents", "path"=".+"},
     *     name="app_documents_file"
     * )
     * @Method("GET")
     */
    public function fileAction($type, $path)
    {
        $this->checkDocumentTypeAccess($type);

        try {
            $document = $this->get('app.documents_repository')->readDocument($type, $path);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('Document not found', $e);
        }

        $response = new Response($document['content']);
        $response->headers->set('Content-Type', $document['mimetype']);

        return $response;
    }

    private function checkDocumentTypeAccess(string $type)
    {
        $committeeManager = $this->get('app.committee_manager');
        if (DocumentRepository::DIRECTORY_HOSTS === $type && !$committeeManager->isCommitteeHost($this->getUser())) {
            throw $this->createNotFoundException();
        }

        $authChecker = $this->get('security.authorization_checker');
        if (DocumentRepository::DIRECTORY_REFERENTS === $type && !$authChecker->isGranted('ROLE_REFERENT')) {
            throw $this->createNotFoundException();
        }
    }
}
