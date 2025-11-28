<?php

declare(strict_types=1);

namespace App\Adherent;

use App\Entity\Committee;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Attribute\Groups;

class AdherentAutocompleteFilter
{
    #[Groups(['filter_write'])]
    public ?string $q = null;

    #[Groups(['filter_write'])]
    public ?Committee $committee = null;

    /**
     * @var Zone[]
     */
    public array $managedZones;

    public array $managedCommitteeUuids;

    #[Groups(['filter_write'])]
    public ?string $tag = null;

    public function __construct(array $managedZones = [], array $managedCommitteeUuids = [])
    {
        $this->managedZones = $managedZones;
        $this->managedCommitteeUuids = $managedCommitteeUuids;
    }
}
