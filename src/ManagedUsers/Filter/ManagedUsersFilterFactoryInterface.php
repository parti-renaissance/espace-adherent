<?php

namespace App\ManagedUsers\Filter;

use App\Entity\Adherent;
use App\ManagedUsers\ManagedUsersFilter;

interface ManagedUsersFilterFactoryInterface
{
    public function support(string $spaceCode): bool;

    public function create(Adherent $adherent, array $zones): ManagedUsersFilter;
}
