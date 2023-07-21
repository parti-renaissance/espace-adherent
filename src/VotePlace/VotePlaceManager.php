<?php

namespace App\VotePlace;

use App\Address\AddressInterface;
use App\Entity\Election\VotePlace;
use App\Repository\Election\VotePlaceRepository;

class VotePlaceManager
{
    private VotePlaceRepository $repository;

    public function __construct(VotePlaceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getVotePlaceWishesByCountryOrPostalCode(
        ?string $assessorCountry,
        ?string $assessorPostalCode,
        ?string $assessorCity
    ): array {
        if (null !== $assessorCountry && AddressInterface::FRANCE !== $assessorCountry) {
            $this->formatVotePlaceWishes($this->repository->findByCountry($assessorCountry));
        }

        if (!empty($assessorPostalCode) && !empty($assessorCity)) {
            return $this->formatVotePlaceWishes($this->repository->findByPostalCode($assessorPostalCode, $assessorCity));
        }

        return [];
    }

    public function getVotePlacesLabelsByIds(array $votePlacesIds): array
    {
        return $this->formatVotePlaceWishes($this->repository->findAllByIds($votePlacesIds));
    }

    private function formatVotePlaceWishes(array $votePlaces): array
    {
        /** @var VotePlace $votePlace */
        foreach ($votePlaces as $votePlace) {
            $choices[$votePlace->getId()] = $votePlace->getLabel();
        }

        return $choices ?? [];
    }
}
