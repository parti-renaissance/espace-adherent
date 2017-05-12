<?php

namespace AppBundle\Controller;

use AppBundle\Documents\DocumentRepository;
use AppBundle\Entity\Adherent;
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
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $isHost = $this->get('app.committee.manager')->isCommitteeHost($adherent);
        $isReferent = $adherent->isReferent();

        $documents = [];
        $documents['adherent'] = $this->get('app.documents_repository')->listAdherentDirectory('/');

        if ($isHost || $isReferent) {
            $documents['host'] = $this->get('app.documents_repository')->listHostDirectory('/');
        }

        if (($isHost || $isReferent) && 'FR' !== strtoupper($adherent->getCountry())) {
            $documents['foreign_host'] = $this->get('app.documents_repository')->listForeignHostDirectory('/');
        }

        if ($isReferent) {
            $documents['referent'] = $this->get('app.documents_repository')->listReferentDirectory('/');
        }

        if ($this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $documents['legislative_candidate'] = $this->get('app.documents_repository')->listLegislativeCandidateDirectory('/');
        }

        return $this->render('documents/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    /**
     * @Route(
     *     "/dossier/{type}/{path}",
     *     requirements={"type"="adherents|animateurs|referents|animateurs-etrangers|candidats-legislatives", "path"=".+"},
     *     name="app_documents_directory"
     * )
     * @Method("GET")
     */
    public function directoryAction($type, $path)
    {
        if (DocumentRepository::DIRECTORY_LEGISLATIVE_CANDIDATES === $type && !$this->isGranted('ROLE_PREVIOUS_ADMIN')) {
            throw $this->createNotFoundException();
        }

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
     *     requirements={"type"="adherents|animateurs|referents|animateurs-etrangers|candidats-legislatives", "path"=".+"},
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
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $isHost = $this->get('app.committee.manager')->isCommitteeHost($adherent);
        $isReferent = $adherent->isReferent();
        $isLegislativeCandidate = $adherent->isLegislativeCandidate();

        if (DocumentRepository::DIRECTORY_HOSTS === $type && !($isHost || $isReferent)) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_FOREIGN_HOSTS === $type && !($isHost || $isReferent || 'FR' !== strtoupper($adherent->getCountry()))) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_REFERENTS === $type && !$isReferent) {
            throw $this->createNotFoundException();
        }

        if (DocumentRepository::DIRECTORY_LEGISLATIVE_CANDIDATES === $type && !$isLegislativeCandidate) {
            throw $this->createNotFoundException();
        }
    }
}
