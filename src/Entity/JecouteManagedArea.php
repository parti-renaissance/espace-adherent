<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'jecoute_managed_areas')]
class JecouteManagedArea extends ZoneManagedArea
{
}
