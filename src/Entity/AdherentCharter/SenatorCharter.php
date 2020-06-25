<?php

namespace App\Entity\AdherentCharter;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Algolia\Index(autoIndex=false)
 */
class SenatorCharter extends AbstractAdherentCharter
{
}
