<?php

namespace App\Normalizer\Indexer;

use App\Entity\TerritorialCouncil\Candidacy;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\CandidacyInterface;

class TerritorialCouncilCandidatureNormalizer extends AbstractDesignationCandidatureNormalizer
{
    protected function getClassName(): string
    {
        return Candidacy::class;
    }

    protected function normalizeElectionEntity(CandidacyInterface $candidacy): array
    {
        /** @var Election $election */
        $election = $candidacy->getElection();
        $coTerr = $election->getTerritorialCouncil();

        return [
            'id' => $coTerr->getId(),
            'name' => $coTerr->getName(),
        ];
    }

    /**
     * @param Candidacy $object
     */
    protected function normalizeCustomFields(CandidacyInterface $object): array
    {
        return [
            'project' => !empty($object->getFaithStatement()),
            'binome_ids' => $object->getBinome() ? [$object->getBinome()->getId()] : null,
        ];
    }
}
