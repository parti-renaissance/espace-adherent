<?php

namespace App\Adherent;

use App\Entity\Committee;
use App\Entity\Geo\Zone;
use Symfony\Component\Serializer\Annotation\Groups;

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

    public function __construct(array $managedZones = [])
    {
        $this->managedZones = $managedZones;
    }
}
