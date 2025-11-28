<?php

declare(strict_types=1);

namespace App\VotingPlatform;

use App\Entity\Adherent;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Repository\VotingPlatform\DesignationRepository;
use App\VotingPlatform\Designation\DesignationTypeEnum;

class ElectionManager
{
    public function __construct(private readonly DesignationRepository $designationRepository)
    {
    }

    /**
     * @return Designation[]
     */
    public function findActiveDesignations(
        Adherent $adherent,
        array $types = [DesignationTypeEnum::LOCAL_ELECTION, DesignationTypeEnum::LOCAL_POLL],
        ?int $limit = null,
        bool $withVoteActiveOnly = false,
    ): array {
        return $this->designationRepository->findAllActiveForAdherent($adherent, $types, $limit, $withVoteActiveOnly);
    }
}
