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
    private array $types = Zone::TYPES;

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): void
    {
        $this->types = array_values(array_intersect(Zone::TYPES, $types));
    }
}
