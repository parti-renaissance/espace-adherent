<?php

namespace App\Documents;

use App\Committee\CommitteeManager;
use App\Entity\Adherent;

class DocumentManager
{
    private $repository;
    private $committeeManager;

    public function __construct(DocumentRepository $repository, CommitteeManager $committeeManager)
    {
        $this->repository = $repository;
        $this->committeeManager = $committeeManager;
    }

    public function listAdherentFiles(Adherent $adherent): array
    {
        $documents = [];
        $documents['adherent'] = $this->repository->listAdherentDirectory('/');

        if ($this->committeeManager->isCommitteeHost($adherent)) {
            $documents['host'] = $this->repository->listHostDirectory('/');
            if ($adherent->isForeignResident()) {
                $documents['foreign_host'] = $this->repository->listForeignHostDirectory('/');
            }
        }

        if ($adherent->isReferent()) {
            $documents['referent'] = $this->repository->listReferentDirectory('/');
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
