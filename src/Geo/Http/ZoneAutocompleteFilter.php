<?php

namespace App\Geo\Http;

use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Annotation\Groups;

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

    #[Groups(['filter_write'])]
    private ?array $types = null;

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
        if ($this->availableForCommittee) {
            return Zone::COMMITTEE_TYPES;
        }

        return Zone::TYPES;
    }
}
