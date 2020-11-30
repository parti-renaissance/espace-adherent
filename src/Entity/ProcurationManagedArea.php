<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="procuration_managed_areas")
 * @ORM\Entity(repositoryClass="App\Repository\ProcurationManagedAreaRepository")
 */
class ProcurationManagedArea extends ManagedArea
{
}
