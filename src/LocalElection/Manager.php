<?php

declare(strict_types=1);

namespace App\LocalElection;

use App\Entity\LocalElection\CandidaciesGroup;
use League\Flysystem\FilesystemOperator;
use Ramsey\Uuid\Uuid;

class Manager
{
    public function __construct(private readonly FilesystemOperator $defaultStorage)
    {
    }

    public function uploadFaithStatementFile(CandidaciesGroup $candidaciesGroup): void
    {
        if (!$candidaciesGroup->file) {
            return;
        }

        if (!$candidaciesGroup->hasFaithStatementFile()) {
            $candidaciesGroup->faithStatementFileName = \sprintf('%s.pdf', Uuid::uuid4());
        }

        $this->defaultStorage->write($candidaciesGroup->getFaithStatementFilePath(), file_get_contents($candidaciesGroup->file->getPathname()));
    }
}
