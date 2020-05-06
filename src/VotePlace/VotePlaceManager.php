<?php

namespace App\VotePlace;

use App\Entity\VotePlace;
use App\Repository\VotePlaceRepository;

class VotePlaceManager
{
    /** @var VotePlaceRepository VotePlaceRepository */
    private $repository;

    public function __construct(VotePlaceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getVotePlaceWishesByCountryOrPostalCode(
        ?string $assessorCountry,
        ?string $assessorPostalCode
    ): array {
        if (null !== $assessorCountry && 'FR' !== $assessorCountry) {
            return $this->getVotePlaceWishesByCountry($assessorCountry);
        }

        if (!empty($assessorPostalCode)) {
            return $this->getVotePlaceWishesByPostalCode($assessorPostalCode);
        }

        return [];
    }

    public function getVotePlaceWishesByPostalCode(string $postalCode): array
    {
        return $this->formatVotePlaceWishes($this->repository->findByPostalCode($postalCode));
    }

    public function getVotePlaceWishesByCountry(string $country): array
    {
        return $this->formatVotePlaceWishes($this->repository->findByCountry($country));
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
