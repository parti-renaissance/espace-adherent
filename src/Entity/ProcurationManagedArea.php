<?php

namespace App\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="procuration_managed_areas")
 * @ORM\Entity(repositoryClass="App\Repository\ProcurationManagerRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class ProcurationManagedArea extends ManagedArea
{
}
