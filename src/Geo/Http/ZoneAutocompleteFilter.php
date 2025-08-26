<?php

namespace App\Geo\Http;

use App\Adherent\MandateTypeEnum;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Attribute\Groups;

class ZoneAutocompleteFilter
{
    #[Groups(['filter_write'])]
    public ?string $q = null;

    #[Groups(['filter_write'])]
    public ?string $spaceType = null;

    #[Groups(['filter_write'])]
    public bool $searchEvenEmptyTerm = false;

    #[Groups(['filter_write'])]
    public bool $availableForCommittee = false;

    #[Groups(['filter_write'])]
    public bool $activeOnly = true;

    public array $committeeUuids = [];

    #[Groups(['filter_write'])]
    private ?array $types = null;

    #[Groups(['filter_write'])]
    public bool $usedByCommittees = false;

    #[Groups(['filter_write'])]
    public ?string $forMandateType = null;

    public function getTypes(): array
    {
        if ($this->types) {
            return array_values(array_intersect($this->getDefaultTypes(), $this->types));
        }

        return $this->getDefaultTypes();
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    private function getDefaultTypes(): array
    {
        if ($this->availableForCommittee || $this->usedByCommittees) {
            return Zone::COMMITTEE_TYPES;
        }

        if ($this->forMandateType && isset(MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$this->forMandateType])) {
            return MandateTypeEnum::ZONE_FILTER_BY_MANDATE[$this->forMandateType]['types'];
        }

        return Zone::TYPES;
    }
}
