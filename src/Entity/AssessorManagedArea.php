<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'assessor_managed_areas')]
class AssessorManagedArea extends ManagedArea
{
}
