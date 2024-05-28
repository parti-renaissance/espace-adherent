<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'assessor_managed_areas')]
#[ORM\Entity]
class AssessorManagedArea extends ManagedArea
{
}
