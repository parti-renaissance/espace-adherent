<?php

declare(strict_types=1);

namespace App\Committee\Filter;

use App\Entity\Committee;
use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeDesignationsListFilter
{
    /**
     * @var Zone[]
     */
    #[Assert\NotBlank]
    private $zones;

    /**
     * @var string|null
     */
    private $committeeName;

    /**
     * @var Committee|null
     */
    private $committee;

    public function __construct(array $zones = [], ?Committee $committee = null)
    {
        $this->zones = $zones;
        $this->committee = $committee;
    }

    /**
     * @return Zone[]
     */
    public function getZones(): array
    {
        return $this->zones;
    }

    /**
     * @param Zone[] $zones
     */
    public function setZones(array $zones): void
    {
        $this->zones = $zones;
    }

    public function getCommitteeName(): ?string
    {
        return $this->committeeName;
    }

    public function setCommitteeName(?string $committeeName): void
    {
        $this->committeeName = $committeeName;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function setCommittee(?Committee $committee): void
    {
        $this->committee = $committee;
    }

    public function toArray(): array
    {
        return [
            'zones' => array_map(static function (Zone $zone): int {
                return $zone->getId();
            }, $this->zones),
            'committeeName' => $this->committeeName,
        ];
    }
}
