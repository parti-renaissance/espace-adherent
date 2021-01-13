<?php

namespace App\Committee\Filter;

use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeDesignationsListFilter
{
    /**
     * @var Zone[]
     *
     * @Assert\NotBlank
     */
    private $zones;

    /**
     * @var string|null
     */
    private $committeeName;

    public function __construct(array $zones)
    {
        $this->zones = $zones;
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
