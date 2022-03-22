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
        ?string $assessorPostalCode,
        ?string $assessorCity
    ): array {
        if (null !== $assessorCountry && 'FR' !== $assessorCountry) {
            return $this->getVotePlaceWishesByCountry($assessorCountry);
        }

        if (!empty($assessorPostalCode) && !empty($assessorCity)) {
            return $this->getVotePlaceWishesByPostalCode($assessorPostalCode, $assessorCity);
        }

        return [];
    }

    public function getVotePlaceWishesByPostalCode(string $postalCode, string $city): array
    {
        return $this->formatVotePlaceWishes($this->repository->findByPostalCode($postalCode, $city));
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
