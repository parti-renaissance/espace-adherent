<?php

declare(strict_types=1);

namespace App\Documents;

use App\Entity\Adherent;

class DocumentManager
{
    private $repository;

    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAdherentFiles(Adherent $adherent): array
    {
        $documents = [];
        $documents['adherent'] = $this->repository->listAdherentDirectory('/');

        if ($adherent->isHost() || $adherent->isSupervisor()) {
            $documents['host'] = $this->repository->listHostDirectory('/');
            if ($adherent->isForeignResident()) {
                $documents['foreign_host'] = $this->repository->listForeignHostDirectory('/');
            }
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
