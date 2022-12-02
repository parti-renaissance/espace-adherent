<?php

namespace App\LocalElection;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\LocalElection;
use App\Repository\LocalElection\LocalElectionRepository;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class Manager
{
    public function __construct(
        private readonly LocalElectionRepository $localElectionRepository,
        private readonly FilesystemInterface $storage
    ) {
    }

    public function getLastLocalElection(Adherent $adherent): ?LocalElection
    {
        $zones = $adherent->getZonesOfType(Zone::DEPARTMENT, true);

        return $zones ? $this->localElectionRepository->findLastForZones($zones) : null;
    }

    public function uploadFaithStatementFile(CandidaciesGroup $candidaciesGroup): void
    {
        if (!$candidaciesGroup->file) {
            return;
        }

        if (!$candidaciesGroup->hasFaithStatementFile()) {
            $candidaciesGroup->faithStatementFileName = sprintf('%s.pdf', Uuid::uuid4());
        }

        $this->storage->put($candidaciesGroup->getFaithStatementFilePath(), file_get_contents($candidaciesGroup->file->getPathname()));
    }
}
