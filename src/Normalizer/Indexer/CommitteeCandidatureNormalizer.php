<?php

namespace App\Normalizer\Indexer;

use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;

class CommitteeCandidatureNormalizer extends AbstractDesignationCandidatureNormalizer
{
    protected function getClassName(): string
    {
        return CommitteeCandidacy::class;
    }

    protected function normalizeElectionEntity(CandidacyInterface $candidacy): array
    {
        /** @var CommitteeElection $election */
        $election = $candidacy->getElection();
        $committee = $election->getCommittee();

        return [
            'id' => $committee->getId(),
            'committee_id' => $committee->getId(),
            'name' => $committee->getName(),
        ];
    }
}
