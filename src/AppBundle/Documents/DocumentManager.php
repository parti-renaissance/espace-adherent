<?php

namespace AppBundle\Documents;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DocumentManager
{
    private $repository;
    private $committeeManager;
    private $authorizationChecker;

    public function __construct(
        DocumentRepository $repository,
        CommitteeManager $committeeManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->repository = $repository;
        $this->committeeManager = $committeeManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function listAdherentFiles(Adherent $adherent): array
    {
        $isHost = $this->committeeManager->isCommitteeHost($adherent);

        $documents = [];
        $documents['adherent'] = $this->repository->listAdherentDirectory('/');

        if ($isHost || $adherent->isReferent()) {
            $documents['host'] = $this->repository->listHostDirectory('/');
            if ($adherent->isForeignResident()) {
                $documents['foreign_host'] = $this->repository->listForeignHostDirectory('/');
            }
        }

        if ($adherent->isReferent()) {
            $documents['referent'] = $this->repository->listReferentDirectory('/');
        }

        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $documents['legislative_candidate'] = $this->repository->listLegislativeCandidateDirectory('/');
        }

        return $documents;
    }

    /**
     * @return Document[]
     */
    public function listDirectory(string $type, string $path): array
    {
        return $this->repository->listDirectory($type, $path);
    }

    public function readDocument(string $type, string $path): array
    {
        return $this->repository->readDocument($type, $path);
    }
}
