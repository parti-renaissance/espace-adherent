<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\Entity\CommitteeCandidacy;
use App\Entity\CommitteeElection;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;
use App\VotingPlatform\Designation\DesignationTypeEnum;

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

    protected function normalizeCustomFields(CandidacyInterface $object): array
    {
        if (DesignationTypeEnum::COMMITTEE_SUPERVISOR === $object->getType()) {
            return [
                'project' => $object->getFaithStatement(),
                'binome_ids' => $object->hasOtherCandidacies() ? [
                    current($object->getOtherCandidacies())->getId(),
                ] : null,
            ];
        }

        return [];
    }
}
