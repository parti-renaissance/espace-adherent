<?php

namespace App\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class Manager
{
    public function __construct(private readonly FilesystemInterface $storage)
    {
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
