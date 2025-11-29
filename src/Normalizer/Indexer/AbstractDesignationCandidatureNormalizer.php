<?php

declare(strict_types=1);

namespace App\Normalizer\Indexer;

use App\Entity\VotingPlatform\Designation\CandidacyInterface;

abstract class AbstractDesignationCandidatureNormalizer extends AbstractIndexerNormalizer
{
    /** @param CandidacyInterface $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $election = $object->getElection();
        $designation = $election->getDesignation();

        return array_merge(
            [
                'id' => $object->getId(),
                'type' => $object->getType(),
                'first_name' => $object->getFirstName(),
                'last_name' => $object->getLastName(),
                'adherent_id' => $object->getAdherent()->getId(),
                'image' => $object->getImagePath(),
                'gender' => $object->getGender(),
                'quality' => $object->getQuality(),
                'created_at' => $this->formatDate($object->getCreatedAt()),
                'updated_at' => $this->formatDate($object->getUpdatedAt()),
                'designation' => [
                    'id' => $designation->getId(),
                    'label' => $designation->getLabel(),
                ],
                'election_entity' => $this->normalizeElectionEntity($object),
                'second_round' => false,
                'presentation' => $object->getBiography(),
                'status' => $object->getStatus(),
            ],
            $this->normalizeCustomFields($object),
        );
    }

    abstract protected function normalizeElectionEntity(CandidacyInterface $candidacy): array;

    protected function normalizeCustomFields(CandidacyInterface $object): array
    {
        return [];
    }
}
