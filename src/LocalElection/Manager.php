<?php

namespace App\LocalElection;

use App\Entity\Adherent;
use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\LocalElection;
use App\Repository\LocalElection\LocalElectionRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\ElectionManager;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class Manager
{
    public function __construct(
        private readonly LocalElectionRepository $localElectionRepository,
        private readonly ElectionManager $electionManager,
        private readonly FilesystemInterface $storage
    ) {
    }

    public function getLastLocalElection(Adherent $adherent): ?LocalElection
    {
        if ($designations = $this->electionManager->findActiveDesignations($adherent, [DesignationTypeEnum::LOCAL_ELECTION])) {
            return $this->localElectionRepository->findByDesignation(current($designations));
        }

        return null;
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
