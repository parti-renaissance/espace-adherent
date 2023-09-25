<?php

namespace App\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\DesignationRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class ElectionManager
{
    private array $cache = [];

    public function __construct(private readonly DesignationRepository $designationRepository)
    {
    }

    /**
     * @return Designation[]
     */
    public function findActiveDesignations(
        Adherent $adherent,
        array $types = [DesignationTypeEnum::LOCAL_ELECTION, DesignationTypeEnum::LOCAL_POLL],
        int $limit = null
    ): array {
        if (!$adherent->isRenaissanceUser()) {
            return [];
        }

        $cacheKey = implode('-', array_merge([$adherent->getId()], $types));

        if (!empty($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        return $this->cache[$cacheKey] = $this->designationRepository->findAllActiveForAdherent($adherent, $types, $limit);
    }
}
